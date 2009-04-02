/**
 * 
 */
package com.dokeos.videoconference;

import java.util.Map;
import java.util.Set;

import org.apache.commons.lang.ArrayUtils;
import org.apache.log4j.Logger;
import org.red5.server.adapter.ApplicationAdapter;
import org.red5.server.api.IClient;
import org.red5.server.api.IConnection;
import org.red5.server.api.IScope;
import org.red5.server.api.Red5;
import org.red5.server.api.service.ServiceUtils;

import com.dokeos.common.config.ConfigurationException;
import com.dokeos.common.util.StringUtil;
import com.dokeos.videoconference.auth.AuthenticationException;
import com.dokeos.videoconference.auth.AuthenticationStrategy;
import com.dokeos.videoconference.classroom.ClassRoom;
import com.dokeos.videoconference.classroom.ClassRoomMemberShipManagement;
import com.dokeos.videoconference.classroom.ClassRoomType;
import com.dokeos.videoconference.classroom.User;
import com.dokeos.videoconference.config.VideoConferenceConfig;

/**
 * @author fburlet
 *
 */
public class VideoConferenceService extends ApplicationAdapter {
    private static final Logger logger = Logger.getLogger(VideoConferenceService.class);
    
    private ClassRoomMemberShipManagement classRoomMemberShipManagement;
    private VideoConferenceConfig videoConferenceConfig;
    private AuthenticationStrategy authenticationStrategy;
    
    public VideoConferenceService(ClassRoomMemberShipManagement classRoomMemberShipManagement, VideoConferenceConfig videoConferenceConfig, AuthenticationStrategy authenticationStrategy) {
        this.classRoomMemberShipManagement = classRoomMemberShipManagement;
        this.videoConferenceConfig = videoConferenceConfig;
        this.authenticationStrategy = authenticationStrategy;
    }
    
    public boolean appConnect(IConnection connection, Object[] params) {
        IClient currentClient = connection.getClient();
        Map<String, Object> connectionParams = connection.getConnectParams();
        if (logger.isDebugEnabled()) {
            logger.debug("Received application connection from host[" + connection.getHost() + "], scope[" + connection.getScope() + "], sessionId[" + connection.getSessionId() + "] by client: " + currentClient + " with parameters [" + ArrayUtils.toString(params) + "]. Connection params[" + connectionParams + "]");
        }
        return super.appConnect(connection, params);
    }
    
    public boolean roomConnect(IConnection connection, Object[] params) {
        IClient currentClient = connection.getClient();
        IScope scope = connection.getScope();
        if (logger.isDebugEnabled()) {
            logger.debug("Received room connection from host[" + connection.getHost() + "], scope[" + scope + "], sessionId[" + connection.getSessionId() + "] by client: " + currentClient + " with parameters [" + ArrayUtils.toString(params) + "]");
        }
        
        if (params.length != 6) {
            // This stops the execution of the method.
            logger.error("Number of parameters invalid [" + params.length + "] instead of 5");
            rejectClient("Number of parameters invalid [" + params.length + "] instead of 5");
        }
        
        String username = (String) params[0];
        String firstName = (String) params[1];
        String lastName = (String) params[2];
        int level = (Integer) params[3];
        String key = (String) params[4];
        String challenge = (String) params[5];
        
        if (StringUtil.isEmpty(username) || StringUtil.isEmpty(key) || StringUtil.isEmpty(challenge)) {
            logger.error("Missing mandatory attribute username, key or challenge");
            rejectClient("Missing mandatory attribute username, key or challenge");
        }
        
        boolean ignoreServerKeys = false;
        try {
            ignoreServerKeys = videoConferenceConfig.getServerKeys().isIgnoreServerKeys();
        } catch (ConfigurationException e) {
            logger.error("Error while determining server key security check: " + e.getMessage());
            rejectClient("Error while determining server key security check: " + e.getMessage());
        }
        
        if (!ignoreServerKeys) {
            String scopeName = scope.getName().split("\\+")[0];
            String keyFromConfig = null;
            try {
                keyFromConfig = videoConferenceConfig.getServerKeys().getServerKey(scopeName);
            } catch (ConfigurationException e) {
                logger.error("Error while getting server key from config file: " + e.getMessage());
                rejectClient("Error while getting server key from config file: " + e.getMessage());
            }

            String plainTextKey = keyFromConfig + challenge;
            try {
                boolean authenticate = authenticationStrategy.authenticate(plainTextKey, key);
                if (!authenticate) {
                    logger.error("Could not authenticate client, keys didn't match.");
                    rejectClient("Could not authenticate client, keys didn't match.");
                }
            } catch (AuthenticationException e) {
                logger.error("Could not authenticate client, exception occured: " + e.getMessage());
                rejectClient("Could not authenticate client, exception occured: " + e.getMessage());
            }
        }
	
        classRoomMemberShipManagement.newUser(currentClient.getId(), username, firstName, lastName, level, key);
                        
        return super.roomConnect(connection, params);
    }

    public String getId() {
        return Red5.getConnectionLocal().getClient().getId();
    }
    
    public Set<User> getCurrentClients() {
        String clientId = Red5.getConnectionLocal().getClient().getId();
        String classRoomName = Red5.getConnectionLocal().getScope().getName();
        if (logger.isDebugEnabled()) {
            logger.debug("Client with id[" + clientId + "] from classRoom[" + classRoomName + "] invoked getCurrentClients().");
        }
        Set<User> users = classRoomMemberShipManagement.getUsers(classRoomName);
        if (logger.isDebugEnabled()) {
            logger.debug("Retrieved [" + users.size() + "] users from classRoom[" + classRoomName + "]");
        }
        return users;
    }

    @Deprecated
    public void sendBroadcast(Object a, Object b, Object c) {
        if (logger.isDebugEnabled())
            logger.debug ("sendBroadcast:"+a+", "+b+", "+c);
        IScope scope = Red5.getConnectionLocal().getScope();
        // Callback not changed because client only handles the first argument.
        ServiceUtils.invokeOnAllConnections(scope, "receiveMessage", new Object[] { c, Red5.getConnectionLocal().getClient().getId() } );
    }

    public void broadcastMessage(Object message) {
        IScope scope = Red5.getConnectionLocal().getScope();
        String fromClientId = Red5.getConnectionLocal().getClient().getId();
	if (logger.isDebugEnabled()) {
		logger.debug ("Broadcasting message from client[" + fromClientId + "] to room[" + scope.getName() + "]");
	}
	// Due to buggy openlazlo, we cannot callback a method with 2 params. We then callback a method with one parameter which is an array.
        ServiceUtils.invokeOnAllConnections(scope, "receiveMessage", new Object[] { new Object[] { fromClientId, message } } );
    }

    public boolean roomJoin(IClient client, IScope scope) {
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + client + "] joined the scope : " + scope);
        }

        ClassRoom classRoom = classRoomMemberShipManagement.newClassRoom(scope.getName(), ClassRoomType.undefined);
        classRoomMemberShipManagement.addUser(classRoom.getName(), client.getId());
        User user = classRoomMemberShipManagement.getUser(client.getId());

        ServiceUtils.invokeOnAllConnections(scope, "clientConnection", new Object[] { user } );
        return true;
    }
    
    public void roomLeave(IClient client, IScope scope) {
        IClient currentClient = Red5.getConnectionLocal().getClient();
        if (logger.isDebugEnabled()) {
            logger.debug("Client: [" + currentClient + "] leaved the scope: [" + scope + "]");
        }
        
        classRoomMemberShipManagement.removeUser(scope.getName(), client.getId());
        ServiceUtils.invokeOnAllConnections(scope, "clientDisconnection", new Object[] { client.getId() } );
        
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
        
        classRoomMemberShipManagement.deleteUser(currentClient.getId());
        
        super.appDisconnect(connection);
    }
    
}
