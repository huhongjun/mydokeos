package com.dokeos.common.config;

import java.io.IOException;
import java.util.Arrays;
import java.util.List;

import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.apache.log4j.Logger;
import org.red5.server.api.scheduling.ISchedulingService;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import com.dokeos.common.util.StringUtil;

/**
 * 
 * @author fburlet
 *
 */
public abstract class XmlConfig implements Config {
    private static final Logger logger = Logger.getLogger(XmlConfig.class);
    private XmlConfigPersister xmlConfigPersister;
    private Document document;
    private XPath xPath;
    
    public XmlConfig(XmlConfigPersister xmlConfigPersister) throws IOException {
        this.xmlConfigPersister = xmlConfigPersister;
        this.document = xmlConfigPersister.load();
        this.xPath = XPathFactory.newInstance().newXPath();
    }
    
    public void refresh() throws IOException {
        logger.debug("Reloading config...");
        this.document = xmlConfigPersister.load();
    }
    
    protected Document getDocument() {
        return document;
    }
    
    protected XmlConfigPersister getXmlConfigPersister() {
        return xmlConfigPersister;
    }
    
    protected XPath getXPath() {
        return xPath;
    }
    
    protected String getStringAttributeValue(Element element, String attributeName, boolean mandatory) throws ConfigurationException {
        if (StringUtil.isEmpty(attributeName, true) || element == null) {
            throw new IllegalArgumentException("Attribute name and element must be specified.");
        }
        String attributeValue = element.getAttribute(attributeName);
        if (StringUtil.isEmpty(attributeValue, true) && mandatory) {
            throw new ConfigurationException("Mandatory attribute[" + attributeName + "] is missing.");
        }
        return attributeValue;
    }
    
    protected List<String> getStringArrayAttributeValue(Element element, String attributeName, String separator, boolean mandatory) throws ConfigurationException {
        String attributeValue = getStringAttributeValue(element, attributeName, mandatory);
        String[] values = attributeValue.split(separator);
        return Arrays.asList(values);
    }
    
    protected Long getLongAttributeValue(Element element, String attributeName, boolean mandatory) throws ConfigurationException {
        String attributeValue = getStringAttributeValue(element, attributeName, mandatory);
        try {
            return StringUtil.isEmpty(attributeValue) ? 0l : Long.valueOf(attributeValue); 
        } catch (NumberFormatException e) {
            throw new ConfigurationException(e);
        }
    }
    
    protected Boolean getBooleanAttributeValue(Element element, String attributeName, boolean mandatory) throws ConfigurationException {
        String attributeValue = getStringAttributeValue(element, attributeName, mandatory);
        try {
            return StringUtil.isEmpty(attributeValue) ? Boolean.FALSE : Boolean.valueOf(attributeValue); 
        } catch (NumberFormatException e) {
            throw new ConfigurationException(e);
        }
    }
    
    protected Integer getIntegerAttributeValue(Element element, String attributeName, boolean mandatory) throws ConfigurationException {
        String attributeValue = getStringAttributeValue(element, attributeName, mandatory);
        try {
            return StringUtil.isEmpty(attributeValue) ? 0 : Integer.valueOf(attributeValue);
        } catch (NumberFormatException e) {
            throw new ConfigurationException(e);
        }
    }
    
    protected Node getMandatoryNode(String xpathExpression) throws ConfigurationException {
        Node n = null;
        try {
            n = (Node) getXPath().evaluate(xpathExpression, getDocument(), XPathConstants.NODE);
            if (n == null) {
                throw new ConfigurationException("Could not find mandatory node of xpath expression[" + xpathExpression + "]");
            }
        } catch (XPathExpressionException e) {
            throw new ConfigurationException(e.getMessage(), e);
        }
        return n;
    }

    protected NodeList getNodeList(String xpathExpression) throws ConfigurationException {
        NodeList nodeList = null;
        try {
            nodeList = (NodeList) getXPath().evaluate(xpathExpression, getDocument(), XPathConstants.NODESET);
        } catch (XPathExpressionException e) {
            throw new ConfigurationException(e.getMessage(), e);
        }
        return nodeList;
    }

    protected NodeList getMandatoryNodeList(String xpathExpression) throws ConfigurationException {
        NodeList nodeList = getNodeList(xpathExpression);
        if (nodeList == null || nodeList.getLength() == 0) {
            throw new ConfigurationException("Could not find mandatory node list of xpath expression[" + xpathExpression + "]");
        }
        return nodeList;
    }

    protected String getNodeValueAsString(Element e) {
        return e.getTextContent();
    }

    protected Boolean getNodeValueAsBoolean(Element e) {
        String nodeValue = getNodeValueAsString(e);
        return StringUtil.isNotEmpty(nodeValue) && "true".equalsIgnoreCase(nodeValue);
    }

}
