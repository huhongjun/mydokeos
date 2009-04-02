package com.dokeos.common.util;

import junit.framework.TestCase;

/**
 * 
 * @author fburlet
 * @author $Author$ (last edit)
 * @version $Revision$
 */
public class StringUtilTest extends TestCase {

    public void testIsEmpty() {
        assertTrue(StringUtil.isEmpty(null, true));
        assertTrue(StringUtil.isEmpty(null, false));
        assertTrue(StringUtil.isEmpty("", true));
        assertTrue(StringUtil.isEmpty("", false));
        assertTrue(StringUtil.isEmpty("   ", true));
        assertFalse(StringUtil.isEmpty("   ", false));
        assertFalse(StringUtil.isEmpty("toto", true));
        assertFalse(StringUtil.isEmpty("toto", false));
        assertFalse(StringUtil.isEmpty("   toto ", true));
        assertFalse(StringUtil.isEmpty("   toto ", false));

        assertTrue(StringUtil.isEmpty(null));
        assertTrue(StringUtil.isEmpty(""));
        assertTrue(StringUtil.isEmpty("   "));
        assertFalse(StringUtil.isEmpty("toto"));
        assertFalse(StringUtil.isEmpty("  toto  "));

        assertFalse(StringUtil.isNotEmpty(null));
        assertFalse(StringUtil.isNotEmpty(""));
        assertFalse(StringUtil.isNotEmpty("   "));
        assertTrue(StringUtil.isNotEmpty("toto"));
        assertTrue(StringUtil.isNotEmpty("  toto  "));

    }

    public void testEquals() {
        assertTrue(StringUtil.equals(null, null));
        assertFalse(StringUtil.equals(null, ""));
        assertFalse(StringUtil.equals("", null));
        assertFalse(StringUtil.equals("toto", null));
        assertFalse(StringUtil.equals(null, "toto"));
        assertTrue(StringUtil.equals("", ""));
        assertTrue(StringUtil.equals("toto", "toto"));
    }
}
