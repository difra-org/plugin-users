<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="recover2">
		<div id="recover2">
			<form action="/recover/submit/{@code}" class="ajaxer" id="auth-password-recovery">
				<h2 id="recovery_title">
					<xsl:value-of select="$locale/auth/forms/recoverPasswordTitle"/>
				</h2>
				<h3>
					<xsl:value-of select="$locale/auth/forms/recoverPasswordFormTitle"/>
				</h3>
				<div class="container password1">
					<div class="holder p">
						<input type="password"
						       name="password1"
						       class="popupFields"
						       placeholder="{$locale/auth/placeholders/password}"/>
					</div>
					<div class="required p" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back">
							<xsl:value-of select="$locale/auth/forms/enterNewPassword"/>
						</div>
					</div>
					<div class="invalid p" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back invalid-text"/>
					</div>
				</div>
				<div class="container password2">
					<div class="holder p">
						<input type="password"
						       name="password2"
						       class="popupFields"
						       placeholder="{$locale/auth/placeholders/againPassword}"/>
					</div>
					<div class="required p" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back">
							<xsl:value-of select="$locale/auth/forms/enterNewPassword2"/>
						</div>
					</div>
					<div class="invalid p" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back invalid-text"/>
					</div>
				</div>
				<div class="button submit">
					<xsl:value-of select="$locale/auth/forms/save"/>
				</div>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
