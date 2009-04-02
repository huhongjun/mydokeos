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
public class ClassRoomManagementImpl implements ClassRoomManagement {
    private static final Logger logger = Logger.getLogger(ClassRoomManagementImpl.class);
    
    private static Map<String, ClassRoom> classRooms = new HashMap<String, ClassRoom>();

    public ClassRoom newClassRoom(String classRoomName, ClassRoomType classRoomType) {
        if (logger.isDebugEnabled()) {
            logger.debug("Creating class room[" + classRoomName + "]");
        }
        ClassRoom classRoom = new ClassRoom();
        classRoom.setName(classRoomName);
        classRoom.setClassRoomType(classRoomType);
        classRooms.put(classRoomName, classRoom);
        return classRoom;
    }

    public ClassRoom getClassRoomByName(String classRoomName) {
        if (logger.isDebugEnabled()) {
            logger.debug("Retrieving class room with name[" + classRoomName + "]");
        }
        return classRooms.get(classRoomName);
    }
    
    public Collection<ClassRoom> getClassRooms() {
        if (logger.isDebugEnabled()) { 
            logger.debug("Retrieving all class rooms");
        }
        return classRooms.values();
    }
    
    public void removeClassRoom(String classRoomName) {
        if (logger.isDebugEnabled()) {
            logger.debug("Removing classRoom with name[" + classRoomName + "]");
        }
        classRooms.remove(classRoomName);
    }
}
