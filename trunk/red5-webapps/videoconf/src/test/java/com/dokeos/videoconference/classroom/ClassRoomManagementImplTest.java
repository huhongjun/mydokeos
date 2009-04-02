/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Collections;

import junit.framework.TestCase;

/**
 * @author fburlet
 *
 */
public class ClassRoomManagementImplTest extends TestCase {
    
    private ClassRoomManagement classRoomManagement;
    
    protected void setUp() throws Exception {
        classRoomManagement = new ClassRoomManagementImpl();
        classRoomManagement.getClassRooms().clear();
    }
    
    public void testCreateClassRoom() {
        assertTrue(classRoomManagement.getClassRooms().isEmpty());
        classRoomManagement.newClassRoom("classroom", ClassRoomType.undefined);
        assertEquals(1, classRoomManagement.getClassRooms().size());
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom"), "classroom", ClassRoomType.undefined);
        classRoomManagement.newClassRoom("classroom", ClassRoomType.undefined);
        assertEquals(1, classRoomManagement.getClassRooms().size());
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom"), "classroom", ClassRoomType.undefined);
        classRoomManagement.newClassRoom("classroom", ClassRoomType.privateRoom);
        assertEquals(1, classRoomManagement.getClassRooms().size());
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom"), "classroom", ClassRoomType.privateRoom);
        classRoomManagement.newClassRoom("classroom_2", ClassRoomType.privateRoom);
        assertEquals(2, classRoomManagement.getClassRooms().size());
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom"), "classroom", ClassRoomType.privateRoom);
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom_2"), "classroom_2", ClassRoomType.privateRoom);
    }
    
    public void testGetClassRoomByName() {
        createClassRooms(5);
        assertEquals(5, classRoomManagement.getClassRooms().size());
        assertNull(classRoomManagement.getClassRoomByName("classroom_10"));
        assertClassRoomEquals(classRoomManagement.getClassRoomByName("classroom_1"), "classroom_1", ClassRoomType.undefined);
        assertEquals(5, classRoomManagement.getClassRooms().size());
    }
    
    public void testRemoveClassRoom() {
        createClassRooms(2);
        classRoomManagement.getClassRoomByName("classroom_1").getUsers().add(new User());
        assertEquals(2, classRoomManagement.getClassRooms().size());
        classRoomManagement.removeClassRoom("classroom_0");
        assertEquals(1, classRoomManagement.getClassRooms().size());
        classRoomManagement.removeClassRoom("classroom_1");
        assertEquals(0, classRoomManagement.getClassRooms().size());
    }
    
    private void createClassRooms(int number) {
        for(int i = 0; i < number; i++) {
            classRoomManagement.newClassRoom("classroom_" + i, ClassRoomType.undefined);
        }
    }
    
    private void assertClassRoomEquals(ClassRoom actualClassRoom, String expectedClassRoomName, ClassRoomType expectedClassRoomType) {
        assertEquals(expectedClassRoomName, actualClassRoom.getName());
        assertEquals(expectedClassRoomType, actualClassRoom.getClassRoomType());
        assertEquals(Collections.EMPTY_SET, actualClassRoom.getUsers());
    }

}
