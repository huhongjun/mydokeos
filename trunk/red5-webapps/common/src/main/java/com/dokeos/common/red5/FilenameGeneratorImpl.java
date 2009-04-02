/**
 * 
 */
package com.dokeos.common.red5;

import java.io.File;
import java.io.IOException;

import org.apache.commons.io.FileUtils;
import org.apache.log4j.Logger;
import org.red5.server.api.IScope;
import org.red5.server.api.stream.IStreamFilenameGenerator;

import com.dokeos.common.config.Config;
import com.dokeos.common.config.ConfigurationException;
import com.dokeos.common.util.StringUtil;


/**
 * <p>
 *  This is a file name generator that helps in storing in a custom directory the recorded streams and playback streams.
 * </p>
 * <p>
 *  This filename generator has a dependency to the {@link Config} interface. Methods getVideoStreamsDir and getVodStreamsDir
 *  should be implemented. Then, in the deployment descriptor of your red5 application (see red5-weba.xml), you'll need to
 *  register these beans as follow:
 * </p>
 * <ul>
 *  <li> First, you will need to register the implementation of the {@link Config} interface: <br />
 *      <pre>
 *        &lt;bean id="myConfigImpl" class="my.package.name.MyConfigImpl" /&gt;
 *      </pre>
 *  <li> Second, you will have to register this bean:
 *      <pre>
 *        &lt;bean id="streamFilenameGenerator" class="com.dokeos.common.red5.FilenameGeneratorImpl" init-method="initialize"&gt;
 *            &lt;constructor-arg ref="myConfigImpl" /&gt;
 *        &lt;/bean&gt;
 *      </pre>
 * </ul>
 * 
 * <p>
 *  The dependency will be auto-magically injected.
 * </p>
 * 
 * @see         <a href="http://www.joachim-bauch.de/tutorials/red5/HOWTO-StreamCustomDirectories.txt">Stream Custom Directories</a>
 * 
 * @author fburlet
 *
 */
public class FilenameGeneratorImpl implements IStreamFilenameGenerator {
    private static final Logger logger = Logger.getLogger(FilenameGeneratorImpl.class);
    
    private String recordedStreamDir;
    private String vodStreamDir;
    
    public FilenameGeneratorImpl(Config config) throws ConfigurationException {
        this.recordedStreamDir = config.getVideoStreamsDir();
        this.vodStreamDir = config.getVodStreamsDir();
    }
    
    public void initialize() throws ConfigurationException {
        createDirectory(recordedStreamDir);
        createDirectory(vodStreamDir);
    }

    public String generateFilename(IScope scope, String name, GenerationType generationType) {
        if (logger.isDebugEnabled()) {
            logger.debug("Invoking generation of filename with params: scope[" + scope + "], name[" + name + "], generationType[" + (generationType != null ? generationType.name() : "null") + "]");
        }
        return generateFilename(scope, name, null, generationType);
    }

    public String generateFilename(IScope scope, String name, String extension, GenerationType generationType) {
        if (logger.isDebugEnabled()) {
            logger.debug("Invoking generation of filename with params: scope[" + scope + "], name[" + name + "], extension[" + extension + "], generationType[" + generationType != null ? generationType.name() : "null"  + "]");
        }
        StringBuffer filename = null;
        if (GenerationType.RECORD.equals(generationType)) {
            if (logger.isDebugEnabled()) {
                logger.debug("* Storing stream in: " + recordedStreamDir);
            }
            filename = new StringBuffer(recordedStreamDir);
        } else if (GenerationType.PLAYBACK.equals(generationType)) {
            if (logger.isDebugEnabled()) {
                logger.debug("* Playing back stream from: " + vodStreamDir);
            }
            filename = new StringBuffer(vodStreamDir);
        } else {
            throw new IllegalStateException("Could not handle generation type[" + generationType + "].");
        }
        
        if (filename.lastIndexOf(File.separator) != (filename.length() - 1)) {
            filename.append(File.separator);
        }
        
        filename.append(name);
        
        if (StringUtil.isNotEmpty(extension)) {
            filename.append(extension);
        }
        
        logger.info("* Generated filename from storage or playback [" + filename.toString() + "]");
        
        return filename.toString();
    }
    
    private void createDirectory(String dirName) {
        logger.info("Creating directory[" + dirName + "] as custom directory for streaming.");
        try {
            File directory = new File(dirName);
            if (directory.exists()) {
                logger.info("Directory[" + dirName + "] already exists, skipping creation.");
            } else {
                FileUtils.forceMkdir(directory);
            }
        } catch (IOException e) {
            throw new RuntimeException("Could not create directory[" + dirName +"]: " + e.getMessage(), e);
        }
    }

    public boolean resolvesToAbsolutePath() {
        return true;
    }
    
}
