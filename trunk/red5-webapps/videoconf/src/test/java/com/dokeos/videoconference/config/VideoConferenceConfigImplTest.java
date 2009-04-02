package com.dokeos.videoconference.config;

import java.util.Map;

import junit.framework.TestCase;

import com.dokeos.common.config.ConfigurationException;
import com.dokeos.common.config.XmlConfigPersisterImpl;

/**
 * 
 * @author fburlet
 * 
 */
public class VideoConferenceConfigImplTest extends TestCase {
    private VideoConferenceConfigImpl videoConferenceConfigImpl;

    public void testLoadServerKeyConfig() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-serverkey.xml", null));
        ServerKeys serverKeys = videoConferenceConfigImpl.getServerKeys();
        assertFalse(serverKeys.isIgnoreServerKeys());
        assertEquals("key1", serverKeys.getServerKey("host1"));
        assertEquals("key2", serverKeys.getServerKey("host2"));
    }
    
    public void testLoadServerKeyConfigWithIgnore() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-serverkey-with-ignore.xml", null));
        ServerKeys serverKeys = videoConferenceConfigImpl.getServerKeys();
        assertTrue(serverKeys.isIgnoreServerKeys());
        assertEquals("key1", serverKeys.getServerKey("host1"));
        assertEquals("key2", serverKeys.getServerKey("host2"));
    }
    
    public void testLoadServerKeyShouldNotFailIfTagNotPresent() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-test.xml", null));
        try {
            videoConferenceConfigImpl.getServerKeys();
            fail("Should have thrown a ConfigurationException");
        } catch (ConfigurationException e) {
            assertEquals("Could not find mandatory node of xpath expression[/videoconference-config/server-keys]", e.getMessage());
        }
    }
    
    public void testLoadEmptyServerKeyShoudThrowsException() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-serverkey-empty.xml", null));
        try {
            videoConferenceConfigImpl.getServerKeys();
            fail("Should have thrown a ConfigurationException");
        } catch (ConfigurationException e) {
            assertEquals("Mandatory attribute[host] is missing.", e.getMessage());            
        }
    }
    
    public void testLoadDisableModCheckOnWB() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-modcheck.xml", null)); 
        assertEquals(true, videoConferenceConfigImpl.isEnabledModeratorCheckOnWhiteBoard());
    }
    
    public void testLoadDisableModCheckOnWBShouldFailIfTagNotPresent() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-test.xml", null));
        try {
            videoConferenceConfigImpl.isEnabledModeratorCheckOnWhiteBoard();
            fail("Sould have thrown a ConfigurationException");
        } catch (ConfigurationException e) {
            assertEquals("Could not find mandatory node of xpath expression[/videoconference-config/moderator-check-on-whiteboard]", e.getMessage());
        }
    }

    public void testLoadEmptyDisableModCheckOnWBShouldReturnFalse() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-modcheck-empty.xml", null)); 
        assertEquals(false, videoConferenceConfigImpl.isEnabledModeratorCheckOnWhiteBoard());
    }
    
    public void testLoadDisableModCheckOnWBWithRandomValueShouldReturnFalse() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-modcheck-randomvalue.xml", null)); 
        assertEquals(false, videoConferenceConfigImpl.isEnabledModeratorCheckOnWhiteBoard());
    }
    
    public void testLoadVideoStreamsDir() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-video-streams-dir.xml", null));
        assertEquals("/opt/dokeos/video", videoConferenceConfigImpl.getVideoStreamsDir());
    }
    
    public void testLoadVodStreamDir() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-vod-streams-dir.xml", null));
        assertEquals("/opt/dokeos/vod", videoConferenceConfigImpl.getVodStreamsDir());
    }
    
    public void testLoadEmptyVideoStreamsDirShouldReturnEmptyString() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-video-streams-dir-empty.xml", null));
        assertEquals("", videoConferenceConfigImpl.getVideoStreamsDir());
    }
    
    public void testLoadEmptyVodStreamsDirShouldReturnEmptyString() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-vod-streams-dir-empty.xml", null));
        assertEquals("", videoConferenceConfigImpl.getVodStreamsDir());
    }
    
    public void testLoadVideoStreamsDirShouldFailIfTagNotPresent() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-test.xml", null));
        try {
            videoConferenceConfigImpl.getVideoStreamsDir();
            fail("Sould have thrown a ConfigurationException");
        } catch (ConfigurationException e) {
            assertEquals("Could not find mandatory node of xpath expression[/videoconference-config/video-streams-dir]", e.getMessage());
        }
    }

    public void testLoadVodStreamsDirShouldFailIfTagNotPresent() throws Exception {
        videoConferenceConfigImpl = new VideoConferenceConfigImpl( new XmlConfigPersisterImpl("videoconference-config-test.xml", null));
        try {
            videoConferenceConfigImpl.getVodStreamsDir();
            fail("Sould have thrown a ConfigurationException");
        } catch (ConfigurationException e) {
            assertEquals("Could not find mandatory node of xpath expression[/videoconference-config/vod-streams-dir]", e.getMessage());
        }
    }
}
