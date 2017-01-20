<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="register">
		<div id="register-popup">
			<form action="/register/submit" class="ajaxer">
                <xsl:call-template name="register-form"/>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
