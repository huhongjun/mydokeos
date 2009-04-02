package com.dokeos.common.config;

import java.io.IOException;

/**
 * 
 * @author fburlet
 * 
 */
public interface Config {
    
    void refresh() throws IOException;
    
    String getVideoStreamsDir() throws ConfigurationException;
    
    String getVodStreamsDir() throws ConfigurationException;

}
