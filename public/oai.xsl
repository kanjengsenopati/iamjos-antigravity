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
        .nav-links { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 14px; }
        .nav-links a { margin-right: 10px; color: #0000CC; text-decoration: underline; }
        .label-cell { background-color: #e0e0ff; font-weight: bold; text-align: right; padding: 3px 10px; width: 180px; vertical-align: top; white-space: nowrap; }
        .value-cell { background-color: #fff; padding: 3px 5px; vertical-align: top; }
        table.info-table { border-collapse: separate; border-spacing: 2px; margin-bottom: 10px; font-size: 14px; }
        .xml-box { background-color: #ffffcc; border: 1px solid #ccc; padding: 15px; font-family: monospace; font-size: 12px; margin-top: 10px; white-space: pre-wrap; }
        .tag { color: maroon; font-weight: bold; }
        .attr { color: red; }
        .val { color: blue; }
        .content { color: black; font-weight: bold; }
        h2 { font-size: 18px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; }
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

    <div style="margin: 15px 0; font-size: 14px;">
        You are viewing an HTML version of the XML OAI response. To see the underlying XML use your web browsers view source option.
    </div>

    <table class="info-table">
        <tr>
            <td class="label-cell">Datestamp of response</td>
            <td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:responseDate"/></td>
        </tr>
        <tr>
            <td class="label-cell">Request URL</td>
            <td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:request"/></td>
        </tr>
    </table>

    <xsl:if test="oai:OAI-PMH/oai:Identify">
        <h3>Repository Identification</h3>
        <table class="info-table">
            <tr><td class="label-cell">Repository Name</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:repositoryName"/></td></tr>
            <tr><td class="label-cell">Base URL</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:baseURL"/></td></tr>
            <tr><td class="label-cell">Protocol Version</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:protocolVersion"/></td></tr>
            <tr><td class="label-cell">Earliest Datestamp</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:earliestDatestamp"/></td></tr>
            <tr><td class="label-cell">Deleted Record Policy</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:deletedRecord"/></td></tr>
            <tr><td class="label-cell">Granularity</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:granularity"/></td></tr>
            <tr><td class="label-cell">Admin Email</td><td class="value-cell"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:adminEmail"/></td></tr>
        </table>
        
        <h2>OAI-Identifier</h2>
        <table class="info-table">
            <tr><td class="label-cell">Scheme</td><td class="value-cell">oai</td></tr>
            <tr><td class="label-cell">Repository Identifier</td><td class="value-cell"><xsl:value-of select="substring-before(substring-after(oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:URL, '//'), '/')"/></td></tr>
            <tr><td class="label-cell">Delimiter</td><td class="value-cell">:</td></tr>
            <tr><td class="label-cell">Sample OAI Identifier</td><td class="value-cell">oai:<xsl:value-of select="substring-before(substring-after(oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:URL, '//'), '/')"/>:article/1</td></tr>
        </table>

        <h2>Unsupported Description Type</h2>
        <div class="xml-box">
&lt;<span class="tag">toolkit</span> <span class="attr">xsi:schemaLocation</span>="<span class="val">http://oai.dlib.vt.edu/OAI/metadata/toolkit http://oai.dlib.vt.edu/OAI/metadata/toolkit.xsd</span>"&gt;
    &lt;<span class="tag">title</span>&gt;<span class="content"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:title"/></span>&lt;/<span class="tag">title</span>&gt;
    &lt;<span class="tag">author</span>&gt;
        &lt;<span class="tag">name</span>&gt;<span class="content"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:author/toolkit:name"/></span>&lt;/<span class="tag">name</span>&gt;
        &lt;<span class="tag">email</span>&gt;<span class="content"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:author/toolkit:email"/></span>&lt;/<span class="tag">email</span>&gt;
    &lt;/<span class="tag">author</span>&gt;
    &lt;<span class="tag">version</span>&gt;<span class="content"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:version"/></span>&lt;/<span class="tag">version</span>&gt;
    &lt;<span class="tag">URL</span>&gt;<span class="content"><xsl:value-of select="oai:OAI-PMH/oai:Identify/oai:description/toolkit:toolkit/toolkit:URL"/></span>&lt;/<span class="tag">URL</span>&gt;
&lt;/<span class="tag">toolkit</span>&gt;
        </div>
    </xsl:if>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
