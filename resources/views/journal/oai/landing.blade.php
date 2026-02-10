<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAI 2.0 Request Results</title>
    <style>
        body {
            font-family: Georgia, "Times New Roman", serif;
            margin: 20px;
            background-color: #ffffff;
            color: #000000;
            line-height: 1.6;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #cccccc;
            padding-bottom: 10px;
        }

        .links {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .links a {
            color: #0000EE;
            text-decoration: underline;
            margin-right: 15px;
        }

        .links a:visited {
            color: #551A8B;
        }

        .links a:hover {
            color: #0000EE;
        }

        .info-text {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid #d0d0d0;
        }

        table {
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #e6e6fa;
            width: 100%;
            max-width: 800px;
        }

        table td {
            padding: 8px;
            border: 1px solid #9999cc;
        }

        table td:first-child {
            font-weight: bold;
            width: 180px;
            background-color: #d9d9f3;
        }

        .error-section {
            margin: 30px 0;
        }

        .error-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .error-table {
            background-color: #ffe6e6;
        }

        .error-table td:first-child {
            background-color: #ffcccc;
        }

        .error-message {
            margin-top: 10px;
            font-size: 14px;
        }

        .about-section {
            margin-top: 40px;
            border-top: 1px solid #cccccc;
            padding-top: 20px;
        }

        .about-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .about-section p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .about-section a {
            color: #0000EE;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>OAI 2.0 Request Results</h1>

    <div class="links">
        <a href="?verb=Identify">Identify</a> |
        <a href="?verb=ListRecords&metadataPrefix=oai_dc">ListRecords</a> |
        <a href="?verb=ListSets">ListSets</a> |
        <a href="?verb=ListMetadataFormats">ListMetadataFormats</a> |
        <a href="?verb=ListIdentifiers&metadataPrefix=oai_dc">ListIdentifiers</a>
    </div>

    <div class="info-text">
        You are viewing an HTML version of the XML OAI response. To see the underlying XML use your web browsers view
        source option. More information about this XSLT is at the <a href="#about">bottom of the page</a>.
    </div>

    <table>
        <tr>
            <td>Datestamp of response</td>
            <td>{{ now()->format('Y-m-d\TH:i:sP') }}</td>
        </tr>
        <tr>
            <td>Request URL</td>
            <td>{{ url()->full() }}</td>
        </tr>
    </table>

    <div class="error-section">
        <h2>OAI Error(s)</h2>
        <p>The request could not be completed due to the following error or errors.</p>

        <table class="error-table">
            <tr>
                <td>Error Code</td>
                <td>badVerb</td>
            </tr>
        </table>

        <div class="error-message">
            Illegal OAI verb
        </div>
    </div>

    <div class="links">
        <a href="?verb=Identify">Identify</a> |
        <a href="?verb=ListRecords&metadataPrefix=oai_dc">ListRecords</a> |
        <a href="?verb=ListSets">ListSets</a> |
        <a href="?verb=ListMetadataFormats">ListMetadataFormats</a> |
        <a href="?verb=ListIdentifiers&metadataPrefix=oai_dc">ListIdentifiers</a>
    </div>

    <div class="about-section" id="about">
        <h2>About the XSLT</h2>
        <p>
            An XSLT file has converted the <a href="http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"
                target="_blank">OAI-PMH 2.0</a> responses into XHTML which looks nice in a browser which supports XSLT
            such as Mozilla, Firebird and Internet Explorer. The XSLT file was created by <a
                href="http://www.gutteridge.org/" target="_blank">Christopher Gutteridge</a> at the University of
            Southampton as part of the <a href="http://www.gnu.org/software/eprints/" target="_blank">GNU EPrints</a>
            system, and is freely redistributable under the <a href="http://www.gnu.org/licenses/gpl.html"
                target="_blank">GPL</a>.
        </p>
        <p>
            If you want to use the XSL file on your own OAI interface you may but due to the way XSLT works you must
            install the XSL file on the same server as the OAI script, you can't just link to this copy.
        </p>
        <p>
            For more information or to download the XSL file please see the <a
                href="http://www.openarchives.org/pmh/tools/xslt/" target="_blank">OAI to XHTML XSLT homepage</a>.
        </p>
    </div>
</body>

</html>
