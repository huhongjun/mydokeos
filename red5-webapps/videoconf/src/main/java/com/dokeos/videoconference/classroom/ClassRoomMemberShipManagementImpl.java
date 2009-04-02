/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Set;


/**
 * @author fburlet
 *
 */
public class ClassRoomMemberShipManagementImpl implements ClassRoomMemberShipManagement {
    private ClassRoomManagement classRoomManagement;
    private UserManagement userManagement;
    
    public ClassRoomMemberShipManagementImpl(ClassRoomManagement classRoomManagement, UserManagement userManagement) {
        this.classRoomManagement = classRoomManagement;
        this.userManagement = userManagement;
    }
    
    public User newUser(String clientId, String username, String firstName, String lastName, int level, String key) {
        User u = userManagement.getUserByClientId(clientId);
        if (u == null) {
            return userManagement.newUser(clientId, username, firstName, lastName, level, key);
        } else {
            return u;
        }
    }
    
    public ClassRoom newClassRoom(String classRoomName, ClassRoomType classRoomType) {
        ClassRoom classRoom = classRoomManagement.getClassRoomByName(classRoomName);
        if (classRoom == null) {
            return classRoomManagement.newClassRoom(classRoomName, classRoomType);
        } else {
            return classRoom;
        }
    }

    public void addUser(String classRoomName, String clientId) {
        ClassRoom classRoom = classRoomManagement.getClassRoomByName(classRoomName);
        if (classRoom == null) {
            throw new IllegalStateException("Cannot add a user to a non existing class room[" + classRoomName + "]");
        }
        User user = userManagement.getUserByClientId(clientId);
        if (user == null) {
            throw new IllegalStateException("Cannot add a non existing user [userid: " + clientId + "] to a classRoom");
        }
        classRoom.getUsers().add(user);
        user.setClassRoom(classRoom);
    }
    
    public void removeUser(String classRoomName, String clientId) {
        ClassRoom classRoom = classRoomManagement.getClassRoomByName(classRoomName);
        if (classRoom == null) {
            throw new IllegalStateException("Cannot remove a user to a non existing class room[" + classRoomName + "]");
        }
        User user = userManagement.getUserByClientId(clientId);
        if (user == null) {
            throw new IllegalStateException("Cannot remove a non existing user [userid: " + clientId + "] from a classRoom");
        }
        classRoom.getUsers().remove(user);
        user.setClassRoom(null);
        if (classRoom.getUsers().isEmpty()) {
            classRoomManagement.removeClassRoom(classRoomName);
        }
    }
    
    public void deleteUser(String clientId) {
        userManagement.removeUser(clientId);
    }
    
    public User getUser(String clientId) {
        return userManagement.getUserByClientId(clientId);
    }
    
    public Set<User> getUsers(String classRoomName) {
        ClassRoom classRoom = classRoomManagement.getClassRoomByName(classRoomName);
        if (classRoom == null) {
            throw new IllegalStateException("Cannot remove list of users from a non existing class room[" + classRoomName + "]");
        }
        return classRoom.getUsers();
    }
}
