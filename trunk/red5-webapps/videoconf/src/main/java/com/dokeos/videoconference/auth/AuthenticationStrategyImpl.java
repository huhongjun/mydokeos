package com.dokeos.videoconference.auth;


/**
 * 
 * @author fburlet
 *
 */
public class AuthenticationStrategyImpl implements AuthenticationStrategy {
    private ChecksumStrategy checksumStrategy;
    
    public AuthenticationStrategyImpl(ChecksumStrategy checksumStrategy) {
        this.checksumStrategy = checksumStrategy;
    }
    
    public boolean authenticate(String plainText, String encodedKey) throws AuthenticationException {
        String checksum = null;
        try {
            checksum = checksumStrategy.computeChecksum(plainText);
        } catch (ChecksumComputationException e) {
            throw new AuthenticationException("Could not compute checksum for given key: " + e.getMessage());
        }
        return checksum != null && checksum.equals(encodedKey); 
    }
}
