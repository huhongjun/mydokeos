package com.dokeos.common.config;

import java.io.IOException;

import org.w3c.dom.Document;

/**
 * This is an interface defining ways to save and load xml document.
 * 
 * @author gjoseph
 * @author fburlet
 * @author $LastChangedBy$ (last edit)
 * @revision $LastChangedRevision$
 */
public interface XmlConfigPersister {
    
    Document load() throws IOException;
    
    void save(Document document) throws IOException;

}
