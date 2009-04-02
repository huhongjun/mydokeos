/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Set;


/**
 * @author fburlet
 *
 */
public interface ClassRoomMemberShipManagement {
    
    User newUser(String clientId, String username, String firstName, String lastName, int level, String key);
    
    ClassRoom newClassRoom(String classRoomName, ClassRoomType classRoomType);

    void addUser(String classRoomName, String clientId);

    void removeUser(String classRoomName, String clientId);
    
    void deleteUser(String clientId);
    
    User getUser(String clientId);
    
    Set<User> getUsers(String classRoomName);
}
