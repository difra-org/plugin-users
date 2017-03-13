<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="passwordChange">
        <form action="/login/password" method="post" class="ajaxer">
            <div class="container">
                <input type="password" name="oldpassword" placeholder="{locale/auth/placeholders/oldPassword}"/>
                <span class="status"/>
            </div>
            <div class="container">
                <input type="password" name="password1" placeholder="{locale/auth/placeholders/newPassword1}"/>
                <span class="status"/>
            </div>
            <div class="container">
                <input type="password" name="password2" placeholder="{locale/auth/placeholders/newPassword2}"/>
                <span class="status"/>
            </div>
            <div class="container">
                <input type="hidden" name="submit" value="1"/>
                <input type="submit" value="{locale/auth/forms/save}"/>
            </div>
        </form>
    </xsl:template>
</xsl:stylesheet>
