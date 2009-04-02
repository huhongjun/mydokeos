/**
 * 
 */
package com.dokeos.common.red5;

import org.jmock.Mock;
import org.jmock.MockObjectTestCase;
import org.red5.server.api.IScope;
import org.red5.server.api.stream.IStreamFilenameGenerator;
import org.red5.server.api.stream.IStreamFilenameGenerator.GenerationType;

import com.dokeos.common.config.Config;

/**
 * @author fburlet
 *
 */
public class FilenameGeneratorImplTest extends MockObjectTestCase {
    private static final String VOD_STREAM_PATH = "/opt/dokeos/vodstream/";
    private static final String VIDEO_STREAM_PATH = "/opt/dokeos/videostream/";
 
    private Mock config;
    private Mock scope;
    
    private IStreamFilenameGenerator filenameGenerator;
    
    public void setUp() throws Exception {
        config = mock(Config.class);
        scope = mock(IScope.class);

        config.expects(once()).method("getVideoStreamsDir").will(returnValue(VIDEO_STREAM_PATH));
        config.expects(once()).method("getVodStreamsDir").will(returnValue(VOD_STREAM_PATH));
        filenameGenerator = new FilenameGeneratorImpl((Config) config.proxy());
 
    }
    
    public void testGenerateFilename() {
        assertEquals(VOD_STREAM_PATH + "name", filenameGenerator.generateFilename((IScope) scope.proxy(), "name", GenerationType.PLAYBACK));
        assertEquals(VIDEO_STREAM_PATH + "name", filenameGenerator.generateFilename((IScope) scope.proxy(), "name", GenerationType.RECORD));
        
        assertEquals(VOD_STREAM_PATH + "name.ext", filenameGenerator.generateFilename((IScope) scope.proxy(), "name", ".ext", GenerationType.PLAYBACK));
        assertEquals(VIDEO_STREAM_PATH + "name.ext", filenameGenerator.generateFilename((IScope) scope.proxy(), "name", ".ext", GenerationType.RECORD));
    }
}
