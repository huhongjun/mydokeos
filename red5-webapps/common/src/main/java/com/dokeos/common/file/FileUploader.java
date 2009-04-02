/**
 * 
 */
package com.dokeos.common.file;

import java.io.File;

/**
 * @author fburlet
 *
 */
public interface FileUploader {
    
    void uploadFile(File file) throws FileUploadException;

}
