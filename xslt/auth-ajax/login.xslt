<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="login">
		<div id="login">
			<form action="/auth/login" class="ajaxer">
				<h2>
					<xsl:value-of select="$locale/auth/forms/loginTitle"/>
				</h2>
				<div class="container">
					<input type="text"
					       name="login"
					       placeholder="{$locale/auth/placeholders/email}"/>
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
					<input type="password"
					       name="password"
					       placeholder="{$locale/auth/placeholders/password}"/>
					<div class="required" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back">
							<xsl:value-of
								select="$locale/auth/forms/errors/passwordRequired"/>
						</div>
					</div>
					<div class="invalid" style="display:none">
						<div class="arrow"/>
						<div class="field-description errorIcon-back invalid-text"/>
					</div>
				</div>

				<div id="remember_me">
					<input name="rememberMe" type="checkbox" id="rememberMe" value="1"/>
					<label for="rememberMe">
						<xsl:value-of select="$locale/auth/forms/rememberMe"/>
					</label>
				</div>

				<div id="remember_password">
					<a href="#"
					   onclick="ajaxer.close(this);ajaxer.query('/recover')">
						<xsl:value-of select="$locale/auth/forms/recoverPassword"/>
					</a>
				</div>
				<div class="clear"/>
				<!-- кнопка логина -->
				<div class="button submit">
					<xsl:value-of select="$locale/auth/forms/enter"/>
				</div>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
