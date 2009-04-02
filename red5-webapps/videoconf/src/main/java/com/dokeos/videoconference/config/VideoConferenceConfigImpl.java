package com.dokeos.videoconference.config;

import java.io.IOException;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import com.dokeos.common.config.ConfigurationException;
import com.dokeos.common.config.XmlConfig;
import com.dokeos.common.config.XmlConfigPersister;

/**
 * 
 * @author fburlet
 *
 */
public class VideoConferenceConfigImpl extends XmlConfig implements VideoConferenceConfig {
    private static final String XPATH_VIDEOCONFERENCE_BASE = "/videoconference-config/";
    
    private static final String XPATH_DISABLE_MOD_CHECK_ON_WB = XPATH_VIDEOCONFERENCE_BASE + "moderator-check-on-whiteboard";
    
    private static final String XPATH_VIDEO_STREAMS_DIR = XPATH_VIDEOCONFERENCE_BASE + "video-streams-dir";
    
    private static final String XPATH_VOD_STREAMS_DIR = XPATH_VIDEOCONFERENCE_BASE + "vod-streams-dir";
    
    private static final String XPATH_SERVERKEYS = XPATH_VIDEOCONFERENCE_BASE + "server-keys";
    private static final String XPATH_SERVERKEYS_IGNORE_ATTR = "ignore";
    private static final String XPATH_SERVERKEY = XPATH_SERVERKEYS + "/server-key";

    private static final String SERVERKEY_ATTRIBUTE_HOST = "host";
    private static final String SERVERKEY_ATTRIBUTE_KEY = "key";
    
    public VideoConferenceConfigImpl(XmlConfigPersister xmlConfigPersister) throws IOException {
        super(xmlConfigPersister);
    }
    
    public ServerKeys getServerKeys() throws ConfigurationException {
        ServerKeys serverKeys = new ServerKeys();
        Node serverKeysNode = getMandatoryNode(XPATH_SERVERKEYS);
        boolean ignoreServerKeys = getBooleanAttributeValue((Element) serverKeysNode, XPATH_SERVERKEYS_IGNORE_ATTR, false);
        serverKeys.setIgnoreServerKeys(ignoreServerKeys);
        NodeList nodes = getNodeList(XPATH_SERVERKEY);
        if (!ignoreServerKeys && (nodes == null || nodes.getLength() == 0)) {
            throw new ConfigurationException("Server key misconfigured: ignore attribute is set to false and not server-key elements are found!"); 
        }
        for(int i = 0; nodes != null && i < nodes.getLength(); i++) {
            Element serverKeyNode = (Element) nodes.item(i);
            String host = getStringAttributeValue(serverKeyNode, SERVERKEY_ATTRIBUTE_HOST, true);
            String key = getStringAttributeValue(serverKeyNode, SERVERKEY_ATTRIBUTE_KEY, true);
            serverKeys.addServerKey(host, key);
        }
        return serverKeys;
    }
    
    public boolean isEnabledModeratorCheckOnWhiteBoard() throws ConfigurationException {
        Node disableModCheckOnWBNode = getMandatoryNode(XPATH_DISABLE_MOD_CHECK_ON_WB);
        return getNodeValueAsBoolean((Element) disableModCheckOnWBNode);  
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
