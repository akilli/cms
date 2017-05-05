<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
    xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0"
    xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
    xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
    xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
    xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"
    xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
    xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
    xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
    xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
    xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
    xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
    xmlns:anim="urn:oasis:names:tc:opendocument:xmlns:animation:1.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:math="http://www.w3.org/1998/Math/MathML"
    xmlns:xforms="http://www.w3.org/2002/xforms"
    xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
    xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
    xmlns:smil="urn:oasis:names:tc:opendocument:xmlns:smil-compatible:1.0"
    xmlns:ooo="http://openoffice.org/2004/office"
    xmlns:ooow="http://openoffice.org/2004/writer"
    xmlns:oooc="http://openoffice.org/2004/calc"
    xmlns:int="http://opendocumentfellowship.org/internal"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    exclude-result-prefixes="office meta config text table draw presentation dr3d chart form script style number anim dc xlink math xforms fo svg smil ooo ooow oooc int #default"
>
    <xsl:output method="xml" indent="yes" omit-xml-declaration="yes" encoding="utf-8" standalone="no"/>

    <!-- Body -->
    <xsl:template match="office:document-content">
        <body>
            <xsl:apply-templates/>
        </body>
    </xsl:template>

    <!-- Linebreak -->
    <xsl:template match="text:line-break">
        <xsl:element name="br"/>
    </xsl:template>

    <!-- Whitespace -->
    <xsl:template match="text:s">
        <xsl:text> </xsl:text>
    </xsl:template>

    <!-- Heading -->
    <xsl:template match="text:h">
        <xsl:if test="node()">
            <!-- text:outline-level is optional, default is 1 -->
            <xsl:variable name="level">
                <xsl:choose>
                    <xsl:when test="not(@text:outline-level)">1</xsl:when>
                    <xsl:when test="@text:outline-level &gt; 6">6</xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@text:outline-level"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <xsl:element name="{concat('h', $level)}">
                <xsl:apply-templates/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <!-- Paragraph -->
    <xsl:template match="text:p">
        <xsl:choose>
            <!-- Remove empty paragraphs -->
            <xsl:when test="count(node())=0"/>
            <!-- Headings -->
            <xsl:when test="@text:style-name='Heading_20_1' and node()">
                <h1><xsl:apply-templates/></h1>
            </xsl:when>
            <xsl:when test="@text:style-name='Heading_20_2' and node()">
                <h2><xsl:apply-templates/></h2>
            </xsl:when>
            <xsl:when test="@text:style-name='Heading_20_3' and node()">
                <h3><xsl:apply-templates/></h3>
            </xsl:when>
            <xsl:when test="@text:style-name='Heading_20_4' and node()">
                <h4><xsl:apply-templates/></h4>
            </xsl:when>
            <xsl:when test="@text:style-name='Heading_20_5' and node()">
                <h5><xsl:apply-templates/></h5>
            </xsl:when>
            <xsl:when test="@text:style-name='Heading_20_6' and node()">
                <h6><xsl:apply-templates/></h6>
            </xsl:when>
            <!-- Images -->
            <xsl:when test="descendant::draw:*">
                <xsl:apply-templates/>
            </xsl:when>
            <!-- Quotations -->
            <xsl:when test="@text:style-name='Quotations' and node()">
                <xsl:element name="blockquote">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:when>
            <!-- Default -->
            <xsl:otherwise>
                <xsl:element name="p">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- List -->
    <xsl:template match="text:list">
        <xsl:variable name="level" select="count(ancestor::text:list)+1"/>
        <!-- the list class is the @text:style-name of the outermost <text:list> element -->
        <xsl:variable name="listClass">
            <xsl:choose>
                <xsl:when test="$level=1">
                    <xsl:value-of select="@text:style-name"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="ancestor::text:list[last()]/@text:style-name"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <!-- Now select the <text:list-level-style-foo> element at this level of nesting for this list -->
        <xsl:variable name="bulletType">
            <xsl:value-of select="local-name(ancestor::*//text:list-style[@style:name=$listClass]/*[@text:level=$level])"/>
        </xsl:variable>
        <!-- emit appropriate list type -->
        <xsl:choose>
            <!-- element ol -->
            <xsl:when test="$bulletType='list-level-style-number'">
                <xsl:element name="ol">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:when>
            <!-- element ul -->
            <xsl:otherwise>
                <xsl:element name="ul">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- List Item -->
    <xsl:template match="text:list-item">
        <xsl:element name="li">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- Table -->
    <xsl:template match="table:table">
        <xsl:element name="table">
            <xsl:element name="colgroup">
                <xsl:apply-templates select="table:table-column"/>
            </xsl:element>
            <xsl:if test="table:table-header-rows/table:table-row">
                <xsl:element name="thead">
                    <xsl:apply-templates select="table:table-header-rows/table:table-row"/>
                </xsl:element>
            </xsl:if>
            <xsl:if test="table:table-footer-rows/table:table-row">
                <xsl:element name="tfoot">
                    <xsl:apply-templates select="table:table-footer-rows/table:table-row"/>
                </xsl:element>
            </xsl:if>
            <xsl:element name="tbody">
                <xsl:apply-templates select="table:table-row"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="table:table-column">
        <xsl:element name="col">
            <xsl:if test="@table:number-columns-repeated">
                <xsl:attribute name="span">
                    <xsl:value-of select="@table:number-columns-repeated"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <!-- Table Row -->
    <xsl:template match="table:table-row">
        <xsl:element name="tr">
            <xsl:apply-templates select="table:table-cell"/>
        </xsl:element>
    </xsl:template>

    <!-- Table Cell -->
    <xsl:template match="table:table-cell">
        <xsl:variable name="n">
            <xsl:choose>
                <xsl:when test="@table:number-columns-repeated != 0">
                    <xsl:value-of select="@table:number-columns-repeated"/>
                </xsl:when>
                <xsl:otherwise>1</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:call-template name="process-table-cell">
            <xsl:with-param name="n" select="$n"/>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="process-table-cell">
        <xsl:param name="n"/>
        <xsl:if test="$n != 0">
            <xsl:element name="td">
                <xsl:if test="@table:number-columns-spanned">
                    <xsl:attribute name="colspan">
                        <xsl:value-of select="@table:number-columns-spanned"/>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="@table:number-rows-spanned">
                    <xsl:attribute name="rowspan">
                        <xsl:value-of select="@table:number-rows-spanned"/>
                    </xsl:attribute>
                </xsl:if>
                <xsl:apply-templates/>
            </xsl:element>
            <xsl:call-template name="process-table-cell">
                <xsl:with-param name="n" select="$n - 1"/>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!-- Link -->
    <xsl:template match="text:a">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- Image -->
    <xsl:template match="draw:image">
        <xsl:element name="img">
            <xsl:attribute name="src">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:attribute name="alt">
                <xsl:value-of select="../@draw:name"/>
            </xsl:attribute>
            <xsl:attribute name="title">
                <xsl:value-of select="../@draw:name"/>
            </xsl:attribute>
            <xsl:choose>
                <!-- office version 1.0 -->
                <xsl:when test="../svg:desc">
                    <xsl:attribute name="longdesc">
                        <xsl:value-of select="../svg:desc"/>
                    </xsl:attribute>
                </xsl:when>
                <!-- office  version 1.2 -->
                <xsl:when test="../svg:title">
                    <xsl:attribute name="longdesc">
                        <xsl:value-of select="../svg:title"/>
                    </xsl:attribute>
                </xsl:when>
            </xsl:choose>
        </xsl:element>
    </xsl:template>

    <!-- Object -->
    <xsl:template match="draw:plugin">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:attribute name="title">
                <xsl:value-of select="../@draw:name"/>
            </xsl:attribute>
            <xsl:value-of select="../@draw:name"/>
        </xsl:element>
    </xsl:template>

    <!-- Subscript -->
    <xsl:template match="text:sub">
        <xsl:element name="sub">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- Superscript -->
    <xsl:template match="text:sup">
        <xsl:element name="sup">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- Insertion -->
    <xsl:template match="text:insertion">
        <xsl:element name="ins">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- Deletion -->
    <xsl:template match="text:deletion">
        <xsl:element name="del">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
