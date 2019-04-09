<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="userList">
        <h2>
            <xsl:value-of select="$locale/auth/adm/h2-title"/>
        </h2>
        <form action="{/root/@controllerUri}" method="get">
            <div class="form-group row">
                <label for="user-search-name" class="col-sm-2 col-form-label">
                    <xsl:value-of select="$locale/auth/adm/search/name"/>
                </label>
                <div class="col-sm-3">
                    <input type="search" name="name" id="user-search-name" value="{search/@name}" class="form-control"/>
                </div>
                <div class="col-sm-1">
                    <input type="submit" class="btn btn-primary" value="{$locale/auth/adm/search/submit}"/>
                </div>
            </div>
        </form>
        <br/>
        <xsl:choose>
            <xsl:when test="not(user)">
                <xsl:value-of select="$locale/auth/adm/users-empty"/>
            </xsl:when>
            <xsl:otherwise>
                <div class="users-stats">
                    <span class="label">
                        <xsl:value-of select="$locale/auth/adm/total"/>
                    </span>
                    <span class="value">
                        <xsl:value-of select="@total"/>
                    </span>
                    <span class="label">
                        <xsl:value-of select="$locale/auth/adm/active"/>
                    </span>
                    <span class="value">
                        <xsl:value-of select="@active"/>
                    </span>
                </div>
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>
                                <xsl:value-of select="$locale/auth/adm/id"/>
                            </th>
                            <th>
                                <xsl:value-of select="$locale/auth/adm/email"/>
                            </th>
                            <th>
                                <xsl:value-of select="$locale/auth/adm/registered"/>
                            </th>
                            <th>
                                <xsl:value-of select="$locale/auth/adm/logged"/>
                            </th>
                            <th>
                                <xsl:value-of select="$locale/auth/adm/flags"/>
                            </th>
                            <th>

                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="user"/>
                    </tbody>
                </table>
                <xsl:apply-templates select="paginator"/>
            </xsl:otherwise>
        </xsl:choose>

        <xsl:if test="/root/content/userList/@pages&gt;1">
            <div class="paginator">
                <xsl:call-template name="paginator">
                    <xsl:with-param name="link">
                        <xsl:value-of select="/root/userList/@link"/>
                    </xsl:with-param>
                    <xsl:with-param name="pages">
                        <xsl:value-of select="/root/userList/@pages"/>
                    </xsl:with-param>
                    <xsl:with-param name="current">
                        <xsl:value-of select="/root/userList/@current"/>
                    </xsl:with-param>
                </xsl:call-template>
            </div>
        </xsl:if>

    </xsl:template>

    <xsl:template match="/root/content/userList/user">
        <tr>
            <td>
                <xsl:value-of select="@id"/>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="not(@login) or @login=''">
                        <xsl:value-of select="@email"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@login"/>
                        <xsl:text> (</xsl:text>
                        <xsl:value-of select="@email"/>
                        <xsl:text>)</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <xsl:value-of select="@registered"/>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when
                            test="@logged='0000-00-00 00:00:00'">
                        <xsl:text>—</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@logged"/>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="@banned=1 and @active=0">
                        <xsl:value-of select="$locale/auth/adm/inactive"/>
                        <xsl:text>,&#160;</xsl:text>
                        <xsl:value-of select="$locale/auth/adm/banned"/>
                    </xsl:when>
                    <xsl:when test="@banned=1">
                        <xsl:value-of select="$locale/auth/adm/banned"/>
                    </xsl:when>
                    <xsl:when test="@active=0">
                        <xsl:value-of select="$locale/auth/adm/inactive"/>
                    </xsl:when>
                    <!--
                                        <xsl:when test="@moderator=1">
                                            <xsl:value-of
                                                    select="$locale/auth/adm/moderator_flag"/>
                                        </xsl:when>
                    -->
                    <xsl:otherwise>
                        <xsl:text>—</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td class="actions">
                <a href="/adm/users/list/activate/{@id}" title="{$locale/auth/adm/activate}">
                    <xsl:attribute name="class">
                        <xsl:text>btn btn-dark ajaxer fas fa-power-off</xsl:text>
                        <xsl:if test="not(@active=0)">
                            <xsl:text> disabled</xsl:text>
                        </xsl:if>
                    </xsl:attribute>
                </a>
                <xsl:text> </xsl:text>

                <xsl:choose>
                    <xsl:when test="@banned=1">
                        <a href="/adm/users/list/unban/{@id}" class="btn btn-light ajaxer fas fa-ban" title="{$locale/auth/adm/unban}"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <a href="/adm/users/list/ban/{@id}" class="btn btn-dark ajaxer fas fa-ban" title="{$locale/auth/adm/ban}"/>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:text> </xsl:text>
                <!--
                                <xsl:choose>
                                    <xsl:when test="@moderator=1">
                                        <a href="/adm/users/list/unmoderator/{@id}"
                                           class="button ajaxer">
                                            <xsl:value-of
                                                    select="$locale/auth/adm/unModerator"/>
                                        </a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <a href="/adm/users/list/moderator/{@id}"
                                           class="button ajaxer">
                                            <xsl:value-of
                                                    select="$locale/auth/adm/moderator"/>
                                        </a>
                                    </xsl:otherwise>
                                </xsl:choose>
                -->
                <a href="/adm/users/list/edit/{@id}" class="btn btn-dark fas fa-edit"/>
            </td>
        </tr>
    </xsl:template>
</xsl:stylesheet>

