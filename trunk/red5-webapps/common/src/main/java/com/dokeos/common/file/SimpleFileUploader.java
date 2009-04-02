/**
 * 
 */
package com.dokeos.common.file;

import java.io.File;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpStatus;
import org.apache.commons.httpclient.methods.PostMethod;
import org.apache.commons.httpclient.methods.multipart.FilePart;
import org.apache.commons.httpclient.methods.multipart.MultipartRequestEntity;
import org.apache.commons.httpclient.methods.multipart.Part;
import org.apache.log4j.Logger;

/**
 * @author fburlet
 *
 */
public class SimpleFileUploader implements FileUploader {
    private static final Logger logger = Logger.getLogger(SimpleFileUploader.class);
    
    private String destination;
    
    public SimpleFileUploader(String destination) {
        this.destination = destination;

    }

    public void uploadFile(File source) throws FileUploadException {
        long size = source.length() / 1024;
        if (!source.exists()) {
            throw new FileUploadException("Cannot non existing file[" + source.getName() + "]");
        }
        PostMethod postMethod = new PostMethod(destination);
        try {
            Part[] parts = {
                new FilePart("file", source),
            };
            postMethod.setRequestEntity(
                new MultipartRequestEntity(parts, postMethod.getParams())
                );
            HttpClient client = new HttpClient();
            client.getHttpConnectionManager().getParams().setConnectionTimeout(5000);
            int status = client.executeMethod(postMethod);
            if (status == HttpStatus.SC_OK) {
                logger.info("File [" + source.getName() + "] of size[" + size  + "kB] has been successfuly posted to [" + destination + "].");
            } else {
                logger.warn("File [" + source.getName() + "] of size[" + size + "kB] has NOT been successfuly posted to [" + destination + "].");
            }
        } catch (Exception e) { 
          throw new FileUploadException("Exception occured while posting file[" + source.getName() + "] to [" + destination + "]: " + e.getMessage());
        } finally {
            postMethod.releaseConnection();
        }
    }
}
