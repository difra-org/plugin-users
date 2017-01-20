<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="userEdit">
        <h2>
            <a href="/adm/users/list">
                <xsl:value-of select="$locale/auth/adm/h2-title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/auth/adm/h3-useredit-common"/>
        </h2>
        <h3>
            <xsl:value-of select="$locale/auth/adm/mainParams"/>
        </h3>
        <xsl:choose>
            <xsl:when test="not(@id)">
                <xsl:value-of select="$locale/auth/adm/user-not-found"/>
            </xsl:when>
            <xsl:otherwise>
                <form method="post" action="/adm/users/list/save/{@id}" class="ajaxer">
                    <table class="form">
                        <colgroup>
                            <col style="width: 250px"/>
                            <col/>
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>
                                    <xsl:value-of select="$locale/auth/adm/email"/>
                                </th>
                                <td>
                                    <input type="text" class="full-width"
                                           name="email" value="{@email}"
                                           id="email"/>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="changePw" class="checkbox_label">
                                        <xsl:value-of select="$locale/auth/adm/change-password"/>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" name="change_pw"
                                           id="changePw"
                                           onchange="changePassEnabler()"/>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <xsl:value-of select="$locale/auth/adm/new-password"/>
                                </th>
                                <td>
                                    <input type="text" class="full-width"
                                           name="new_pw" id="newPw"
                                           disabled="disabled"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <xsl:if test="addon_fields">

                        <h3>
                            <xsl:value-of select="$locale/auth/adm/addonFields"/>
                        </h3>

                        <table class="form">
                            <colgroup>
                                <col style="width: 250px"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th>
                                    <xsl:value-of select="$locale/auth/adm/fieldName"/>
                                </th>
                                <th>
                                    <xsl:value-of select="$locale/auth/adm/fieldValue"/>
                                </th>
                            </tr>

                            <xsl:for-each select="addon_fields/field">
                                <tr>
                                    <td>
                                        <input type="text" name="fieldName[]"
                                               value="{@name}"
                                               class="full-width"/>
                                    </td>
                                    <td>
                                        <input type="text" name="fieldValue[]"
                                               value="{@value}"
                                               class="full-width"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </table>
                    </xsl:if>


                    <div class="form-buttons">
                        <input type="submit" value="{$locale/auth/adm/save}"/>
                    </div>
                </form>
                <xsl:if test="item/info">
                    <h3>
                        <xsl:value-of select="$locale/auth/adm/h3-useredit-info"/>
                    </h3>
                    <table>
                        <xsl:for-each select="item/info/@*">
                            <xsl:variable name="name" select="name()"/>
                            <tr>
                                <th>
                                    <xsl:value-of select="$locale/auth/info/*[name()=$name]"/><xsl:text>:</xsl:text>
                                </th>
                                <td>
                                    <xsl:value-of select="."/>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </table>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>

