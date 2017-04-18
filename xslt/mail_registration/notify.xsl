<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html" indent="no" encoding="utf-8"/>
    <xsl:template match="/mail">
        <html>
            <body>
                <xsl:value-of select="/mail/locale/auth/mail/registration/welcome1"
                              disable-output-escaping="yes"/>
                <xsl:value-of select="@host"/>
                <xsl:value-of select="/mail/locale/auth/mail/registration/welcome2"
                              disable-output-escaping="yes"/>
                <br/>
                <br/>
                <xsl:value-of select="/mail/locale/auth/mail/registration/username"
                              disable-output-escaping="yes"/>
                <xsl:value-of select="@username"/>
                <br/>
                <!--
                <xsl:value-of select="/mail/locale/auth/mail/registration/password"
                          disable-output-escaping="yes"/>
                <xsl:value-of select="@password"/>
                <br/>
                -->
                <br/>
                <xsl:choose>
                    <xsl:when test="@confirm='moderate'">
                        <xsl:value-of select="/mail/locale/auth/mail/registration/moderate"/>
                        <br/>
                        <br/>
                    </xsl:when>
                    <xsl:when test="@confirm='email'">
                        <xsl:value-of select="/mail/locale/auth/mail/registration/email1"
                                      disable-output-escaping="yes"/>
                        <a href="{@fullhost}/register/activate/{@code}">
                            <xsl:value-of select="@fullhost"/>
                            <xsl:text>/register/activate/</xsl:text>
                            <xsl:value-of select="@code"/>
                        </a>
                        <xsl:value-of select="/mail/locale/auth/mail/registration/email2"
                                      disable-output-escaping="yes"/>
                        <br/>
                        <xsl:value-of select="/mail/locale/auth/mail/registration/email3"
                                      disable-output-escaping="yes"/>
                        <br/>
                        <br/>
                    </xsl:when>
                </xsl:choose>
                <xsl:value-of select="/mail/locale/auth/mail/registration/legal1"
                              disable-output-escaping="yes"/>
                <a href="mailto:support@{@host}">
                    <xsl:text>support@</xsl:text><xsl:value-of select="@host"/>
                </a>
                <xsl:value-of select="/mail/locale/auth/mail/registration/legal2"
                              disable-output-escaping="yes"/>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
