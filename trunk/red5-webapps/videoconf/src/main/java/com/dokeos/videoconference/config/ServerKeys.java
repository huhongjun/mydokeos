/**
 * 
 */
package com.dokeos.videoconference.config;

import java.util.HashMap;
import java.util.Map;

/**
 * @author fburlet
 *
 */
public class ServerKeys {
    private boolean ignoreServerKeys;
    private Map<String, String> serverKeys = new HashMap<String, String>();
    
    public boolean isIgnoreServerKeys() {
        return ignoreServerKeys;
    }
    
    public void setIgnoreServerKeys(boolean ignoreServerKeys) {
        this.ignoreServerKeys = ignoreServerKeys;
    }
    
    public void addServerKey(String host, String key) {
        serverKeys.put(host, key);
    }
    
    public String getServerKey(String host) {
        return serverKeys.get(host);
    }
}
