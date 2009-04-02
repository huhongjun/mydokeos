/**
 * 
 */
package com.dokeos.videoconference.auth;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

import org.apache.commons.codec.binary.Hex;

/**
 * @author fburlet
 *
 */
public class MD5ChecksumStrategy implements ChecksumStrategy {

    public String computeChecksum(String s) throws ChecksumComputationException {
        if (s == null) {
            throw new ChecksumComputationException("Cannot compute checksum for null string.");
        }
        
        String computedMd5 = null;
        MessageDigest md = null;
        try {
            md = MessageDigest.getInstance("MD5");
            byte[] digest = md.digest(s.getBytes());
            char[] hex = Hex.encodeHex(digest);
            computedMd5 = new String(hex);
        } catch (NoSuchAlgorithmException e) {
            throw new ChecksumComputationException(e);
        }
        return computedMd5;
    }
}
