/**
 * 
 */
package com.dokeos.common.file;

/**
 * This is an exception that has to be thrown when something goes 
 * wrong when we upload a file.
 * 
 * @author fburlet
 *
 */
public class FileUploadException extends Exception {

    public FileUploadException() {
    }

    public FileUploadException(String message) {
        super(message);
    }

    public FileUploadException(Throwable cause) {
        super(cause);
    }

    public FileUploadException(String message, Throwable cause) {
        super(message, cause);
    }
}
