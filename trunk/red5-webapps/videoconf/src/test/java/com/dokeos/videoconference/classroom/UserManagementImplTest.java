/**
 * 
 */
package com.dokeos.videoconference.classroom;

import java.util.Collection;

import junit.framework.TestCase;

/**
 * @author fburlet
 *
 */
public class UserManagementImplTest extends TestCase {
    
    private UserManagement userManagement;
    
    protected void setUp() throws Exception {
        userManagement = new UserManagementImpl();
        userManagement.getUsers().clear();
    }
    
    public void testNewUser() {
        assertTrue(userManagement.getUsers().isEmpty());
        userManagement.newUser("1", "username_1", "firstname_1", "lastname_1", 1, "key_1");
        assertEquals(1, userManagement.getUsers().size());
        userManagement.newUser("2", "username_2", "firstname_2", "lastname_2", 1, "key_2");
        assertEquals(2, userManagement.getUsers().size());
        userManagement.newUser("1", "username_3", "firstname_3", "lastname_3", 1, "key_3");
        assertEquals(2, userManagement.getUsers().size());
    }
    
    public void testGetUserByClientId() {
        createUsers(5);
        assertEquals(5, userManagement.getUsers().size());
        assertUser(userManagement.getUserByClientId("2"), "2", "username_2", "firstname_2", "lastname_2", 1, "key_2");
        assertNull(userManagement.getUserByClientId("10"));
        assertEquals(5, userManagement.getUsers().size());
        
    }
    
    public void testRemoveUser() {
        createUsers(5);
        assertEquals(5, userManagement.getUsers().size());
        userManagement.removeUser("2");
        Collection<User> users = userManagement.getUsers(); 
        assertEquals(4, users.size());
        for(User u : users) {
            if (u.getId().equals("2")) {
                fail("User with id 2 should have been removed.");
            }
        }
        
        userManagement.removeUser("10");
        assertEquals(4, userManagement.getUsers().size());
    }
    
    private void createUsers(int number) {
        for(int i = 0; i < number; i++) {
            userManagement.newUser(Integer.toString(i), "username_" + i, "firstname_" + i, "lastname_" + i, 1, "key_" + i);
        }
    }
    
    private void assertUser(User actualUser, String expectedId, String expectedUsername, String expectedFirstname, String expectedLastname, int expectedLevel, String expectedKey) {
        assertEquals(expectedId, actualUser.getId());
        assertEquals(expectedUsername, actualUser.getUsername());
        assertEquals(expectedFirstname, actualUser.getFirstName());
        assertEquals(expectedLastname, actualUser.getLastName());
        assertEquals(expectedLevel, actualUser.getLevel());
        assertEquals(expectedKey, actualUser.getKey());
        assertEquals(false, actualUser.isModerator());
        assertEquals(null, actualUser.getClassRoom());
    }
    
}
