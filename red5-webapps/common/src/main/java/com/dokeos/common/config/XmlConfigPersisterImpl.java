package com.dokeos.common.config;

import org.apache.commons.io.FileUtils;
import org.apache.log4j.Logger;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.Result;
import javax.xml.transform.Source;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.dom.DOMSource;
import javax.xml.transform.stream.StreamResult;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;

/**
 * This class implements ways of saving and loading xml files from the file system.
 * 
 * @author gjoseph
 * @author fburlet
 * @author $LastChangedBy$ (last edit)
 * @revision $LastChangedRevision$
 */
public class XmlConfigPersisterImpl implements XmlConfigPersister {
    private static final Logger logger = Logger.getLogger(XmlConfigPersisterImpl.class);

    private String filename;
    private String saveableLocation;

    public XmlConfigPersisterImpl(String filename) {
        this(filename, null);
    }

    public XmlConfigPersisterImpl(String filename, String saveableLocation) {
        this.filename = filename;
        this.saveableLocation = saveableLocation;
    }
    
    /**
     * This method first tries to load externalized configuration file <code>filename</code> from the
     * <code>saveableLocation</code>. If the file does not exist or cannot be read, then the default
     * configuration file located in the application classpath is loaded. 
     */
    public Document load() throws IOException {
        logger.debug("Loading " + filename);
        InputStream in;
        File f = new File(saveableLocation, filename);
        if (!f.exists() || !f.canRead()) {
            logger.debug("No custom config file, loading defaults");
            in = getClass().getResourceAsStream("/" + filename);
        } else {
            in = new FileInputStream(f);
        }

        if (in == null) {
            throw new IllegalArgumentException("Can't load config from " + filename + ". Must be a valid filename or classpath resource.");
        }
        
        try {
            DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
            DocumentBuilder db = dbf.newDocumentBuilder();
            return db.parse(in);
        } catch (ParserConfigurationException e) {
            throw new RuntimeException(e);
        } catch (SAXException e) {
            throw new RuntimeException(e);
        } finally {
            in.close();
        }
    }
    
    /**
     * This method saves the an xml document to an external location.<br />
     * <ul>
     *  <li>If the external location is not specified, this method throws an <code>IllegalStateException</code>.</li> 
     *  <li>If the external location is specified but does not exist, then this external location is created before saving the file.</li>
     * </ul>
     * @param doc - the document that must be saved.
     * @throws IOException - if error occurs while saving the file (ex: no write permission).
     */
    public void save(Document doc) throws IOException {
        if (saveableLocation == null) {
            throw new IllegalStateException("Can't save config file, no saveableLocation as been set.");
        }
        File dir = new File(saveableLocation);
        FileUtils.forceMkdir(dir);
        File f = new File(dir, filename);
        final FileWriter out = new FileWriter(f);
        try {
            Result result = new StreamResult(out);
            Source source = new DOMSource(doc);
            final TransformerFactory transformerFactory = TransformerFactory.newInstance();
            Transformer xformer = transformerFactory.newTransformer();
            xformer.transform(source, result);

        } catch (TransformerException e) {
            throw new RuntimeException(e);
        } finally {
            out.close();
        }
    }
}
