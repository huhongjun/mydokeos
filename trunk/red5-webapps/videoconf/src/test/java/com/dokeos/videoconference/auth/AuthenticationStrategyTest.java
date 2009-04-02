/**
 * 
 */
package com.dokeos.videoconference.auth;

import org.jmock.Mock;
import org.jmock.MockObjectTestCase;

/**
 * @author fburlet
 *
 */
public class AuthenticationStrategyTest extends MockObjectTestCase {
    private Mock checksumStrategyMock;
    private AuthenticationStrategy authenticationStrategy;

    protected void setUp() throws Exception {
        checksumStrategyMock = mock(ChecksumStrategy.class);
        authenticationStrategy = new AuthenticationStrategyImpl((ChecksumStrategy) checksumStrategyMock.proxy());
    }

    public void testAuthenticateWithNonEmptyStringAsPlainText() throws Exception {
        checksumStrategyMock.expects(once()).method("computeChecksum").with(eq("toto")).will(returnValue("totoEncoded"));
        assertTrue(authenticationStrategy.authenticate("toto", "totoEncoded"));
    }

    public void testAuthenticateWithEmptyStringAsPlainText() throws Exception {
        checksumStrategyMock.expects(once()).method("computeChecksum").with(eq("")).will(returnValue("emptyStrEncoded"));
        assertTrue(authenticationStrategy.authenticate("", "emptyStrEncoded"));
    }

    public void testAuthenticateWithNullStringAsPlainText() {
        checksumStrategyMock.expects(once()).method("computeChecksum").with(NULL).will(throwException(new ChecksumComputationException("message")));
        try {
            authenticationStrategy.authenticate(null, "toto");
        } catch (AuthenticationException e) {
            assertEquals("Could not compute checksum for given key: message", e.getMessage());
        }
    }

    public void testAuthenticateWithNullEncodedKey() throws Exception {
        checksumStrategyMock.expects(once()).method("computeChecksum").with(eq("toto")).will(returnValue("totoEncoded"));
        assertFalse(authenticationStrategy.authenticate("toto", null));
    }

    public void testAuthenticateWithNullArguments() throws Exception {
        checksumStrategyMock.expects(once()).method("computeChecksum").with(NULL).will(throwException(new ChecksumComputationException("message")));
        try {
            authenticationStrategy.authenticate(null, null);
        } catch (AuthenticationException e) {
            assertEquals("Could not compute checksum for given key: message", e.getMessage());
        }

    }
}
