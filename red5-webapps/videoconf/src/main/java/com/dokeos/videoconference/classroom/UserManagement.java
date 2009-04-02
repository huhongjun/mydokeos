/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Collection;


/**
 * @author fburlet
 *
 */
public interface UserManagement {
    
    User newUser(String clientId, String username, String firstName, String lastName, int level, String key);

    User getUserByClientId(String clientId);
    
    void removeUser(String clientId);
    
    Collection<User> getUsers();

}
