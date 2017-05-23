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

    <!-- body -->
    <xsl:template match="office:document-content">
        <body>
            <xsl:apply-templates/>
        </body>
    </xsl:template>

    <!-- Whitespace -->
    <xsl:template match="text:s">
        <xsl:text> </xsl:text>
    </xsl:template>

    <!-- br -->
    <xsl:template match="text:line-break">
        <xsl:element name="br"/>
    </xsl:template>

    <!-- h1-h6 -->
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

    <!-- p -->
    <xsl:template match="text:p">
        <xsl:choose>
            <!-- Remove empty paragraphs -->
            <xsl:when test="count(node())=0"/>
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

    <!-- span -->
    <xsl:template match="text:span">
        <xsl:variable name="spanClass">
            <xsl:value-of select="@text:style-name"/>
        </xsl:variable>
        <xsl:variable name="spanStyle" select="//style:style[@style:name=$spanClass]/*"/>
        <xsl:variable name="spanB" select="$spanStyle[@fo:font-weight='bold']"/>
        <xsl:variable name="spanI" select="$spanStyle[@fo:font-style='italic']"/>
        <xsl:variable name="spanU" select="$spanStyle[@style:text-underline-style]"/>
        <xsl:choose>
            <xsl:when test="$spanB and $spanI and $spanU">
                <b><i><u><xsl:apply-templates/></u></i></b>
            </xsl:when>
            <xsl:when test="$spanB and $spanI">
                <b><i><xsl:apply-templates/></i></b>
            </xsl:when>
            <xsl:when test="$spanB and $spanU">
                <b><u><xsl:apply-templates/></u></b>
            </xsl:when>
            <xsl:when test="$spanI and $spanU">
                <i><u><xsl:apply-templates/></u></i>
            </xsl:when>
            <xsl:when test="$spanB">
                <b><xsl:apply-templates/></b>
            </xsl:when>
            <xsl:when test="$spanI">
                <i><xsl:apply-templates/></i>
            </xsl:when>
            <xsl:when test="$spanU">
                <u><xsl:apply-templates/></u>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- a -->
    <xsl:template match="text:a">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- img -->
    <xsl:template match="draw:image">
        <xsl:element name="img">
            <xsl:attribute name="src">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:attribute name="alt">
                <xsl:value-of select="../@draw:name"/>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <!-- object -->
    <xsl:template match="draw:plugin">
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="@xlink:href"/>
            </xsl:attribute>
            <xsl:value-of select="../@draw:name"/>
        </xsl:element>
    </xsl:template>

    <!-- ol or ul -->
    <xsl:template match="text:list">
        <xsl:variable name="level" select="count(ancestor::text:list)+1"/>
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
        <xsl:variable name="bulletType">
            <xsl:value-of select="local-name(//text:list-style[@style:name=$listClass]/*[@text:level=$level])"/>
        </xsl:variable>
        <xsl:choose>
            <!-- ol -->
            <xsl:when test="$bulletType='list-level-style-number'">
                <xsl:element name="ol">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:when>
            <!-- ul -->
            <xsl:otherwise>
                <xsl:element name="ul">
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- li -->
    <xsl:template match="text:list-item">
        <xsl:element name="li">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>

    <!-- table -->
    <xsl:template match="table:table">
        <xsl:element name="table">
            <!-- colgroup -->
            <xsl:element name="colgroup">
                <xsl:apply-templates select="table:table-column"/>
            </xsl:element>
            <!-- thead -->
            <xsl:if test="table:table-header-rows/table:table-row">
                <xsl:element name="thead">
                    <xsl:apply-templates select="table:table-header-rows/table:table-row"/>
                </xsl:element>
            </xsl:if>
            <!-- tfoot -->
            <xsl:if test="table:table-footer-rows/table:table-row">
                <xsl:element name="tfoot">
                    <xsl:apply-templates select="table:table-footer-rows/table:table-row"/>
                </xsl:element>
            </xsl:if>
            <!-- tbody -->
            <xsl:element name="tbody">
                <xsl:apply-templates select="table:table-row"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <!-- col -->
    <xsl:template match="table:table-column">
        <xsl:element name="col">
            <xsl:if test="@table:number-columns-repeated">
                <xsl:attribute name="span">
                    <xsl:value-of select="@table:number-columns-repeated"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <!-- tr -->
    <xsl:template match="table:table-row">
        <xsl:element name="tr">
            <xsl:apply-templates select="table:table-cell"/>
        </xsl:element>
    </xsl:template>

    <!-- td -->
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
</xsl:stylesheet>
