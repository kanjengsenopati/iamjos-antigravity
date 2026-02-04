<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:toolkit="http://oai.dlib.vt.edu/OAI/metadata/toolkit">
<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>

<xsl:template match="/">
<html>
<head>
    <title>OAI 2.0 Request Results</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 16px; margin: 20px; color: #000; }
        h1 { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        h2 { font-size: 18px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; }
        .nav-links { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 14px; }
        .nav-links a { margin-right: 10px; color: #0000CC; text-decoration: underline; }
        .label-cell { background-color: #e0e0ff; font-weight: bold; text-align: right; padding: 3px 10px; width: 150px; vertical-align: top; white-space: nowrap; }
        .value-cell { background-color: #fff; padding: 3px 5px; vertical-align: top; }
        table.info-table { border-collapse: separate; border-spacing: 2px; margin-bottom: 10px; font-size: 14px; }
        .xml-box { background-color: #ffffcc; border: 1px solid #ccc; padding: 15px; font-family: monospace; font-size: 12px; margin-top: 10px; white-space: pre-wrap; }
        .mini-btn { background-color: #e0e0ff; font-size: 12px; padding: 1px 4px; margin-left: 5px; color: #000; text-decoration: none; border: 1px solid #aaa; }
    </style>
</head>
<body>
    <h1>OAI 2.0 Request Results</h1>

    <div class="nav-links">
        <a href="?verb=Identify">Identify</a> | 
        <a href="?verb=ListRecords&amp;metadataPrefix=oai_dc">ListRecords</a> | 
        <a href="?verb=ListSets">ListSets</a> | 
        <a href="?verb=ListMetadataFormats">ListMetadataFormats</a> | 
        <a href="?verb=ListIdentifiers&amp;metadataPrefix=oai_dc">ListIdentifiers</a>
    </div>

    <table class="info-table">
        <tr><td class="label-cell">Datestamp of response</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:responseDate"/></td></tr>
        <tr><td class="label-cell">Request URL</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:request"/></td></tr>
    </table>

    <xsl:if test="oai:OAI-PMH/oai:error">
        <h2 style="color:red">OAI Error: <xsl:value-of select="oai:OAI-PMH/oai:error/@code"/></h2>
        <p><xsl:value-of select="oai:OAI-PMH/oai:error"/></p>
    </xsl:if>

    <xsl:if test="oai:OAI-PMH/oai:Identify">
        <h3>Repository Identification</h3>
        <table class="info-table">
            <tr><td class="label-cell">Repository Name</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:repositoryName"/></td></tr>
            <tr><td class="label-cell">Base URL</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:baseURL"/></td></tr>
            <tr><td class="label-cell">Protocol Version</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:protocolVersion"/></td></tr>
            <tr><td class="label-cell">Earliest Datestamp</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:earliestDatestamp"/></td></tr>
            <tr><td class="label-cell">Admin Email</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:adminEmail"/></td></tr>
        </table>
    </xsl:if>

    <xsl:if test="oai:OAI-PMH/oai:ListSets">
        <p>Request was of type ListSets.</p>
        <xsl:for-each select="oai:OAI-PMH/oai:ListSets/oai:set">
            <h2>Set</h2>
            <table class="info-table">
                <tr><td class="label-cell">setName</td><td class="value-cell"><xsl:value-of select="oai:setName"/></td></tr>
                <tr>
                    <td class="label-cell">setSpec</td>
                    <td class="value-cell">
                        <xsl:value-of select="oai:setSpec"/>
                        <a href="?verb=ListIdentifiers&amp;metadataPrefix=oai_dc&amp;set={oai:setSpec}" class="mini-btn">Identifiers</a>
                        <a href="?verb=ListRecords&amp;metadataPrefix=oai_dc&amp;set={oai:setSpec}" class="mini-btn">Records</a>
                    </td>
                </tr>
            </table>
        </xsl:for-each>
    </xsl:if>

    <xsl:if test="oai:OAI-PMH/oai:ListIdentifiers">
        <p>Request was of type ListIdentifiers.</p>
        <xsl:for-each select="oai:OAI-PMH/oai:ListIdentifiers/oai:header">
            <h2>OAI Record Header</h2>
            <table class="info-table">
                <tr>
                    <td class="label-cell">OAI Identifier</td>
                    <td class="value-cell">
                        <xsl:value-of select="oai:identifier"/>
                        <a href="?verb=GetRecord&amp;metadataPrefix=oai_dc&amp;identifier={oai:identifier}" class="mini-btn">oai_dc</a>
                        <a href="?verb=ListMetadataFormats&amp;identifier={oai:identifier}" class="mini-btn">formats</a>
                    </td>
                </tr>
                <tr><td class="label-cell">Datestamp</td><td class="value-cell"><xsl:value-of select="oai:datestamp"/></td></tr>
                <tr>
                    <td class="label-cell">setSpec</td>
                    <td class="value-cell"><xsl:value-of select="oai:setSpec"/></td>
                </tr>
            </table>
        </xsl:for-each>
    </xsl:if>

    <div style="margin-top: 20px; font-size: 12px; color: gray; border-top:1px solid #ccc; padding-top:10px;">
        IAMJOS OAI-PMH System (XSLT Rendered)
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
