/**
 * 
 */
package com.dokeos.videoconference.auth;

import junit.framework.TestCase;

/**
 * @author fburlet
 *
 */
public class MD5ChecksumStrategyTest extends TestCase {
    private ChecksumStrategy checksumStrategy;
    
    protected void setUp() throws Exception {
        checksumStrategy = new MD5ChecksumStrategy();
    }
    
    public void testMD5ChecksumStrategy() throws Exception {
        assertEquals("f71dbe52628a3f83a77ab494817525c6", checksumStrategy.computeChecksum("toto"));
        assertEquals("d41d8cd98f00b204e9800998ecf8427e", checksumStrategy.computeChecksum(""));
        try {
            checksumStrategy.computeChecksum(null);
            fail("Should have thrown an exception");
        } catch (ChecksumComputationException e) {
            assertEquals("Cannot compute checksum for null string.", e.getMessage());
        }
    }

}
