package com.dokeos.videoconference.auth;

public interface ChecksumStrategy {
    
    public String computeChecksum(String s) throws ChecksumComputationException;

}
