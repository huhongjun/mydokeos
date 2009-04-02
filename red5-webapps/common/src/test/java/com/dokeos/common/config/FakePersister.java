package com.dokeos.common.config;

import org.w3c.dom.Document;
import org.xml.sax.SAXException;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import java.io.ByteArrayInputStream;
import java.io.IOException;

/**
 * @author gjoseph
 * @author fburlet
 * @author $LastChangedBy$ (last edit)
 * @revision $LastChangedRevision$
 */
class FakePersister extends XmlConfigPersisterImpl {
    static final String XML_1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" +
            "<properties>\n";
    static final String XML_2 =
            "<property name=\"foo\">\n" +
            "   bar   \n" +
            "</property>\n";
    static final String XML_3 =
            "<property name=\"entities\">\n" +
            "  &gt;bar&lt;   \n" +
            "</property>\n" +
            "<property name=\"inline\" value=\"inline-value\"/>\n" +
            "<property name=\"inline-and-content\" value=\"inline-value-2\">\n" +
            "IGNORE\n" +
            "</property>\n" +
            "<property name=\"somecdata\"><![CDATA[\n" +
            "stuff <> fluff\n" +
            "]]>\n" +
            "</property>\n" +
            "<property name=\"commented\"><!--comment should be ignored-->\n" +
            "baz\n" +
            "</property>\n" +
            "<property name=\"some-array\">a,b,c</property>\n";
    static final String XML_4 = "<property name=\"empty-content\"></property>\n";
    static final String XML_5 = "<property name=\"empty-cdata\"><![CDATA[\n" +
            "  \n  " +
            "]]>\n" +
            "</property>\n" +
            "<property name=\"empty-attr\" value=\"\"/>\n" +
            "<property name=\"no-attr\"/>\n" +
            "</properties>\n";

    private static final String XML = XML_1 + XML_2 + XML_3 + XML_4 + XML_5;

    public FakePersister(String filename) {
        super(filename);
    }

    public FakePersister(String filename, String saveableLocation) {
        super(filename, saveableLocation);
    }

    public Document load() throws IOException {
        ByteArrayInputStream in = new ByteArrayInputStream(XML.getBytes());
        try {
            DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
            DocumentBuilder db = dbf.newDocumentBuilder();
            return db.parse(in);
        } catch (ParserConfigurationException e) {
            throw new RuntimeException(e);
        } catch (SAXException e) {
            throw new RuntimeException(e);
        }
    }
}
