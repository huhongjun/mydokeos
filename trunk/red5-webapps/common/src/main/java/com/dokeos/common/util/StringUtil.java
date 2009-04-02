package com.dokeos.common.util;

/**
 * 
 * @author fburlet
 * @author $Author$ (last edit)
 * @version $Revision$
 */
public class StringUtil {

    public static boolean isEmpty(String s) {
        return isEmpty(s, true);
    }

    public static boolean isNotEmpty(String s) {
        return !isEmpty(s);
    }

    public static boolean isEmpty(String s, boolean trim) {
        return s == null || "".equals( trim ? s.trim() : s );
    }

    public static boolean equals(String s1, String s2) {
        if (s1 == null) {
            return s2 == null;
        } else {
            return s1.equals(s2);
        }
    }
}
