/**
 * 
 */
package com.dokeos.videoconference.auth;

/**
 * @author fburlet
 *
 */
public class AuthenticationException extends Exception {

    public AuthenticationException() {
        super();
    }

    public AuthenticationException(String message, Throwable cause) {
        super(message, cause);
    }

    public AuthenticationException(String message) {
        super(message);
    }

    public AuthenticationException(Throwable cause) {
        super(cause);
    }
}
