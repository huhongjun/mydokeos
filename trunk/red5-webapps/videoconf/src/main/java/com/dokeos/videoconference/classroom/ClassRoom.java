/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Set;


/**
 * @author fburlet
 *
 */
public class ClassRoom implements Serializable {
    private String name;
    private ClassRoomType classRoomType;
    private Set<User> users = new HashSet<User>();
    
    public String getName() {
        return name;
    }
    
    public void setName(String name) {
        this.name = name;
    }
    
    public ClassRoomType getClassRoomType() {
        return classRoomType;
    }
    
    public void setClassRoomType(ClassRoomType classRoomType) {
        this.classRoomType = classRoomType;
    }
    
    public Set<User> getUsers() {
        return users;
    }
    
    public void setUsers(Set<User> users) {
        this.users = users;
    }

    public int hashCode() {
        final int prime = 31;
        int result = 1;
        result = prime * result + ((classRoomType == null) ? 0 : classRoomType.hashCode());
        result = prime * result + ((name == null) ? 0 : name.hashCode());
        return result;
    }

    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null)
            return false;
        if (getClass() != obj.getClass())
            return false;
        final ClassRoom other = (ClassRoom) obj;
        if (classRoomType == null) {
            if (other.classRoomType != null)
                return false;
        } else if (!classRoomType.equals(other.classRoomType))
            return false;
        if (name == null) {
            if (other.name != null)
                return false;
        } else if (!name.equals(other.name))
            return false;
        return true;
    }
    
}
