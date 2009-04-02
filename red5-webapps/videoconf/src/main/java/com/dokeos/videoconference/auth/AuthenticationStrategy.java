package com.dokeos.videoconference.auth;

/**
 * 
 * @author fburlet
 *
 */
public interface AuthenticationStrategy {
    
    public boolean authenticate(String plainText, String encodedKey) throws AuthenticationException;

}
