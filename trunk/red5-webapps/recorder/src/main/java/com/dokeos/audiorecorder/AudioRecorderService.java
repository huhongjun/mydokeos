/**
 * 
 */
package com.dokeos.audiorecorder;

import java.io.File;
import java.util.HashMap;
import java.util.Map;

import org.apache.commons.lang.ArrayUtils;
import org.apache.log4j.Logger;
import org.red5.server.adapter.ApplicationAdapter;
import org.red5.server.api.IClient;
import org.red5.server.api.IConnection;
import org.red5.server.api.IScope;
import org.red5.server.api.Red5;
import org.red5.server.api.stream.IBroadcastStream;
import org.red5.server.api.stream.ISubscriberStream;

import com.dokeos.audiorecorder.config.AudioRecorderConfig;
import com.dokeos.common.file.FileUploadException;
import com.dokeos.common.file.FileUploader;
import com.dokeos.common.file.SimpleFileUploader;
import com.dokeos.common.util.StringUtil;

/**
 * This is a service for the audio recording.
 * This service mainly handles clients that are connected.
 * 
 * Inherited methods are implemented but only for
 * logging or let's say... debugging purpose :o)
 * 
 * No real magic here since recording is working out of the box with red5.
 * 
 * Files are directly uploaded to a given server (provided as connection
 * parameter) once the recording is over.
 * 
 * Generated files are stored in the directories provided in the config file.  
 * 
 * @author fburlet
 *
 */
public class AudioRecorderService extends ApplicationAdapter {
    private static final Logger logger = Logger.getLogger(AudioRecorderService.class);

    private AudioRecorderConfig audioRecorderConfig;
    
    private static Map<String, String> map = new HashMap<String, String>();
    
    public AudioRecorderService(AudioRecorderConfig audioRecorderConfig) {
        this.audioRecorderConfig = audioRecorderConfig;
    }
    
    public boolean appConnect(IConnection connection, Object[] params) {
        IClient currentClient = connection.getClient();
        Map<String, Object> connectionParams = connection.getConnectParams();
        if (logger.isDebugEnabled()) {
            logger.debug("Received application connection from host[" + connection.getHost() + "], scope[" + connection.getScope() + "], sessionId[" + connection.getSessionId() + "] by client: " + currentClient + " with parameters [" + ArrayUtils.toString(params) + "]. Connection params[" + connectionParams + "]");
        }
        
        if (params.length == 0) {
            rejectClient("Required parameter missing: url.");
        }
        
        String destination = (String) params[0];
        if (StringUtil.isEmpty(destination)) {
            rejectClient("Required parameter url is missing.");
        }
        
        map.put(new String(currentClient.getId()), destination);
        
        return super.appConnect(connection, params);
    }
    
    public boolean roomConnect(IConnection connection, Object[] params) {
        IClient currentClient = connection.getClient();
        IScope scope = connection.getScope();
        if (logger.isDebugEnabled()) {
            logger.debug("Received room connection from host[" + connection.getHost() + "], scope[" + scope + "], sessionId[" + connection.getSessionId() + "] by client: " + currentClient + " with parameters [" + ArrayUtils.toString(params) + "]");
        }
        return super.roomConnect(connection, params);
    }

    public boolean roomJoin(IClient client, IScope scope) {
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + client + "] joined the scope : " + scope);
        }
        return super.roomJoin(client, scope);
    }
    
    public void roomLeave(IClient client, IScope scope) {
        IClient currentClient = Red5.getConnectionLocal().getClient();
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + currentClient + "] leaved the scope: [" + scope + "]");
        }
        super.roomLeave(client, scope);
    }
    
    public void roomDisconnect(IConnection connection) {
        IClient currentClient = connection.getClient();
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + currentClient + "] disconnected from room [" +connection.getScope() + "]");
        }
        super.roomDisconnect(connection);
    }
    
    public void appDisconnect(IConnection connection) {
        IClient currentClient = connection.getClient();
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + currentClient + "] disconnected from application");
        }
        super.appDisconnect(connection);
    }
    
    public void streamRecordStart(IBroadcastStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream record start called : stream : " + stream.toString());
        }
        super.streamRecordStart(stream);
    }
    
    public void streamSubscriberStart(ISubscriberStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream subscriber start called : stream : " + stream.toString());
        }
        super.streamSubscriberStart(stream);
    }
    
    public void streamSubscriberClose(ISubscriberStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream subscriber close called : stream : " + stream.toString());
        }
        super.streamSubscriberClose(stream);
    }

    public void streamBroadcastClose(IBroadcastStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream broadcast close called : stream : " + stream.toString());
        }
        super.streamBroadcastClose(stream);

        String clientId = Red5.getConnectionLocal().getClient().getId();
        String destination = map.get(clientId);
        
        String savedFilename = stream.getSaveFilename();
        File savedFile = new File(savedFilename);
        try {
            FileUploader fileUploader = new SimpleFileUploader(destination);
            fileUploader.uploadFile(savedFile);
        } catch (FileUploadException e) {
            // TODO: Check what to do here ?? Is rejecting the client good choice ?
            logger.error("File couldn't have been uploaded: " + e.getMessage());
            rejectClient("File couldn't have been uploaded: " + e.getMessage());
        } finally {
            if (logger.isDebugEnabled()) {
                logger.debug("Deleting uploaded file: " + savedFilename);
            }
            savedFile.delete();
        }
    }
    
    public void streamPublishStart(IBroadcastStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream publish start called : stream : " + stream.toString());
        }
        super.streamPublishStart(stream);
    }
    
    public void streamBroadcastStart(IBroadcastStream stream) {
        if (logger.isDebugEnabled()) {
            logger.debug("Stream braodcast start called : stream : " + stream.toString());
        }
        super.streamBroadcastStart(stream);
    }
}
