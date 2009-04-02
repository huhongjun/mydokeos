/**
 * 
 */
package com.dokeos.videoconference.classroom;

import org.jmock.Mock;
import org.jmock.MockObjectTestCase;

/**
 * @author fburlet
 *
 */
public class ClassRoomMemberShipManagementImplTest extends MockObjectTestCase {
    
    private Mock classRoomManagement;
    private Mock userManagement;
    private ClassRoomMemberShipManagement classRoomMemberShipManagement;
    
    protected void setUp() throws Exception {
        classRoomManagement = mock(ClassRoomManagement.class);
        userManagement = mock(UserManagement.class);
        
        classRoomMemberShipManagement = new ClassRoomMemberShipManagementImpl((ClassRoomManagement) classRoomManagement.proxy(), (UserManagement) userManagement.proxy());
    }
    
    public void testAddUser() {
        ClassRoom classRoom = new ClassRoom();
        classRoomManagement.expects(once()).method("getClassRoomByName").with(stringContains("classRoom")).will(returnValue(classRoom));
        User u = new User();
        userManagement.expects(once()).method("getUserByClientId").with(stringContains("1")).will(returnValue(u));
        classRoomMemberShipManagement.addUser("classRoom", "1");
        assertEquals(1, classRoom.getUsers().size());
    }
    
    public void testRemoveUser() {
        ClassRoom classRoom = new ClassRoom();
        User u1 = new User();
        u1.setId("1");
        u1.setUsername("username_1");
        User u2 = new User();
        u2.setId("2");
        u2.setUsername("username_2");
        classRoom.getUsers().add(u1);
        classRoom.getUsers().add(u2);
        classRoomManagement.expects(atLeastOnce()).method("getClassRoomByName").with(stringContains("classRoom")).will(returnValue(classRoom));
        userManagement.expects(once()).method("getUserByClientId").with(stringContains("2")).will(returnValue(u2));

        classRoomMemberShipManagement.removeUser("classRoom", "2");
        assertEquals(1, classRoom.getUsers().size());
        
        userManagement.expects(once()).method("getUserByClientId").with(stringContains("1")).will(returnValue(u1));
        classRoomManagement.expects(once()).method("removeClassRoom").with(stringContains("classRoom")).isVoid();
        classRoomMemberShipManagement.removeUser("classRoom", "1");
        assertEquals(0, classRoom.getUsers().size());
    }
}
