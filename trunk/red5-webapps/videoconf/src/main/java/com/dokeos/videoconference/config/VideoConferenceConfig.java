package com.dokeos.videoconference.config;

import com.dokeos.common.config.Config;
import com.dokeos.common.config.ConfigurationException;

/**
 * 
 * @author fburlet
 * @author $Author$ (last edit)
 * @revision $Revision$
 */
public interface VideoConferenceConfig extends Config {
    
    ServerKeys getServerKeys() throws ConfigurationException;
    
    boolean isEnabledModeratorCheckOnWhiteBoard() throws ConfigurationException;

}
