<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="recover">
		<div id="recover">
			<form action="/recover" class="ajaxer">
				<h2 id="recovery_title">
					<xsl:value-of select="$locale/auth/forms/recoverPasswordTitle"/>
				</h2>
				<div class="container">
					<div class="holder">
						<input type="text"
							   name="email"
							   placeholder="{$locale/auth/placeholders/email}"/>
					</div>
					<div class="required" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back">
							<xsl:value-of select="$locale/auth/forms/errors/emailRequired"/>
						</div>
					</div>
					<div class="invalid" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back invalid-text"/>
					</div>
				</div>
				<div class="container">
					<div class="holder">
						<input type="text"
							   name="email"
							   placeholder="{$locale/auth/placeholders/captcha}"/>
					</div>
					<div class="required" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back">
							<xsl:value-of select="$locale/auth/forms/errors/captchaRequired"/>
						</div>
					</div>
					<div class="invalid" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back invalid-text"/>
					</div>
				</div>
				<div class="button submit">
					<xsl:value-of select="$locale/auth/forms/recover"/>
				</div>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
