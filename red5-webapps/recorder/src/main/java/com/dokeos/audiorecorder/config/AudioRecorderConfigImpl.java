/**
 * 
 */
package com.dokeos.audiorecorder.config;

import java.io.IOException;

import org.w3c.dom.Element;
import org.w3c.dom.Node;

import com.dokeos.common.config.ConfigurationException;
import com.dokeos.common.config.XmlConfig;
import com.dokeos.common.config.XmlConfigPersister;

/**
 * @author fburlet
 *
 */
public class AudioRecorderConfigImpl extends XmlConfig implements AudioRecorderConfig {
    private static final String XPATH_AUDIORECORDER_BASE = "/recorder-config/";
    private static final String XPATH_VIDEO_STREAMS_DIR = XPATH_AUDIORECORDER_BASE + "video-streams-dir";
    
    private static final String XPATH_VOD_STREAMS_DIR = XPATH_AUDIORECORDER_BASE + "vod-streams-dir";

    
    public AudioRecorderConfigImpl(XmlConfigPersister xmlConfigPersister) throws IOException {
        super(xmlConfigPersister);
    }

    public String getVideoStreamsDir() throws ConfigurationException {
        Node videoStreamsDirNode = getMandatoryNode(XPATH_VIDEO_STREAMS_DIR);
        return getNodeValueAsString((Element) videoStreamsDirNode);
    }
    
    public String getVodStreamsDir() throws ConfigurationException {
        Node vodStreamsDirNode = getMandatoryNode(XPATH_VOD_STREAMS_DIR);
        return getNodeValueAsString((Element) vodStreamsDirNode);
    }

}
