/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Collection;
import java.util.HashMap;
import java.util.Map;

import org.apache.log4j.Logger;


/**
 * @author fburlet
 *
 */
public class UserManagementImpl implements UserManagement {
    private static final Logger logger = Logger.getLogger(UserManagementImpl.class);
    
    private static Map<String, User> users = new HashMap<String, User>(); // <clientid, user> 
    
    public User newUser(String clientId, String username, String firstName, String lastName, int level, String key) {
        if (logger.isDebugEnabled()) {
            logger.debug("Creating new user with clientId[" + clientId + "], username[" + username + "], firstname[" + firstName + "], lastname[" + lastName + "], level[" + level + "] and key[" + key + "]");
        }
        User user = new User();
        user.setId(clientId);
        user.setUsername(username);
        user.setFirstName(firstName);
        user.setLastName(lastName);
        user.setLevel(level);
        user.setKey(key);
        users.put(clientId, user);
        if (logger.isInfoEnabled()) {
            logger.info("Created user: " + user);
        }
        return user;            
    }
    
    public User getUserByClientId(String clientId) {
        if (logger.isDebugEnabled()) {
            logger.debug("Retrieving user with clientId[" + clientId + "]");
        }
        return users.get(clientId);
    }
    
    public void removeUser(String clientId) {
        if (logger.isDebugEnabled()) {
            logger.debug("Removing user with clientId[" + clientId + "] from users list");
        }
        users.remove(clientId);
    }
    
    public Collection<User> getUsers() {
        return users.values();
    }
}
