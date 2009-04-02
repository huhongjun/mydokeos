package com.dokeos.common.config;

import org.jmock.Mock;
import org.jmock.MockObjectTestCase;
import org.w3c.dom.Document;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;

/**
 * @author gjoseph
 * @author fburlet
 * @author $LastChangedBy$ (last edit)
 * @revision $LastChangedRevision$
 */
public class XmlConfigPersisterImplTest extends MockObjectTestCase {
    final Mock docMock = mock(Document.class);

    public void testDoesntSaveIfNoLocationSet() throws IOException {
        final XmlConfigPersisterImpl cp = new XmlConfigPersisterImpl("foo.xml");
        try {
            cp.save((Document) docMock.proxy());
            fail();
        } catch (IllegalStateException e) {
            assertEquals("Can't save config file, no saveableLocation as been set.", e.getMessage());
        }
    }

    public void testStoresInLocationSavesOriginalContentAsIs() throws IOException {
        final String destinationDirectory = System.getProperty("java.io.tmpdir");
        final FakePersister cp = new FakePersister("foo.xml", destinationDirectory);
        final Document doc = cp.load();
        cp.save(doc);
        final String expected = FakePersister.XML_1 +
                FakePersister.XML_2 +
                FakePersister.XML_3 +
                "<property name=\"empty-content\"/>\n" +
                FakePersister.XML_5;
        File actualSavedFile = new File(destinationDirectory, "foo.xml");
        assertFileContent(expected, actualSavedFile.getAbsolutePath());
        actualSavedFile.delete();
    }

    private void assertFileContent(final String expected, final String pathname) throws IOException {
        File f = new File(pathname);
        final BufferedReader reader = new BufferedReader(new FileReader(f));
        StringBuilder buff = new StringBuilder();
        String line;
        while ((line = reader.readLine()) != null) {
            buff.append(line);
            buff.append("\n");
        }
        assertEquals(expected, buff.toString());
    }
}
