package com.dokeos.videoconference.classroom;

import java.util.Collection;



public interface ClassRoomManagement {
    
    ClassRoom getClassRoomByName(String name);
    
    ClassRoom newClassRoom(String classRoomName, ClassRoomType classRoomType);
    
    Collection<ClassRoom> getClassRooms();
    
    void removeClassRoom(String classRoomName);

}
