/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.io.Serializable;

import org.red5.annotations.DontSerialize;

import com.dokeos.common.util.StringUtil;

/**
 * @author fburlet
 *
 */
public class User implements Serializable {
    private String id;
    private String username;
    private String firstName;
    private String lastName;
    private boolean isModerator;
    @DontSerialize
    private ClassRoom classRoom;
    // Not used yet
    private int level;
    private String key;
    
    public User() {
    }
    
    public String getId() {
        return id;
    }
    
    public void setId(String id) {
        this.id = id;
    }
    
    public String getUsername() {
        return username;
    }
    
    public void setUsername(String username) {
        this.username = username;
    }
    
    public String getFirstName() {
        return firstName;
    }
    
    public void setFirstName(String firstName) {
        this.firstName = firstName;
    }
    
    public String getLastName() {
        return lastName;
    }
    
    public void setLastName(String lastName) {
        this.lastName = lastName;
    }
    
    public boolean isModerator() {
        return isModerator;
    }
    
    public void setModerator(boolean isModerator) {
        this.isModerator = isModerator;
    }
    
    public ClassRoom getClassRoom() {
        return classRoom;
    }
    
    public void setClassRoom(ClassRoom classRoom) {
        this.classRoom = classRoom;
    }
    
    public int getLevel() {
        return level;
    }
    
    public void setLevel(int level) {
        this.level = level;
    }
    
    public String getKey() {
        return key;
    }
    
    public void setKey(String key) {
        this.key = key;
    }

    public int hashCode() {
        final int prime = 31;
        int result = 1;
        result = prime * result + ((id == null) ? 0 : id.hashCode());
        result = prime * result + ((key == null) ? 0 : key.hashCode());
        result = prime * result + ((username == null) ? 0 : username.hashCode());
        return result;
    }

    public boolean equals(Object obj) {
        if (!(obj instanceof User)) {
            return false;
        }
        final User u = (User) obj;
        return StringUtil.equals(username, u.getUsername());
    }

}
