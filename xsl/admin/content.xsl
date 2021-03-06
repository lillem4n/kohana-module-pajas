<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'content'" />
				<xsl:with-param name="text"   select="'List content'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'content/add_content'" />
				<xsl:with-param name="action" select="'add_content'" />
				<xsl:with-param name="text"   select="'Add content'" />
			</xsl:call-template>

		</ul>
	</xsl:template>


	<xsl:template match="/">
		<xsl:if test="/root/meta/action = 'index'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'List content'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'add_content'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'Add content'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'edit_content'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'Edit content'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List content -->
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="medium_row">Content ID</th>
					<th>Tags</th>
					<th>Content</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="contents/content">
					<xsl:sort select="tags/tag/name" />
					<xsl:sort select="@id" />
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="@id" /></td>
						<td>
							<xsl:for-each select="tags/tag">
								<xsl:value-of select="name" />
								<xsl:if test="position() != last()">
									<xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</td>
						<td><xsl:value-of select="concat(substring(content,1,60), '...')" /></td>
						<td>
							<xsl:text>[</xsl:text>
							<a>
							  <xsl:attribute name="href">
								  <xsl:text>content/edit_content/</xsl:text>
								  <xsl:value-of select="@id" />
							  </xsl:attribute>
							  <xsl:text>Edit</xsl:text>
							</a>
							<xsl:text>] [</xsl:text>
							<a>
							  <xsl:attribute name="href">
								  <xsl:text>content/rm_content/</xsl:text>
								  <xsl:value-of select="@id" />
							  </xsl:attribute>
							  <xsl:text>Delete</xsl:text>
							</a>
							<xsl:text>]</xsl:text>
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="content[../meta/controller = 'content' and (../meta/action = 'add_content' or ../meta/action = 'edit_content')]">
		<form method="post">
			<xsl:if test="../meta/action = 'add_content'">
				<xsl:attribute name="action">content/add_content</xsl:attribute>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_content'">
				<xsl:attribute name="action">
					<xsl:text>content/edit_content/</xsl:text>
					<xsl:value-of select="content_id" />
				</xsl:attribute>
			</xsl:if>

			<h2>Content</h2>

			<textarea class="full_size" rows="30" id="content" name="content"><xsl:value-of select="content" /></textarea>

			<h2>Tags</h2>
			<p>Tag name: Tag value (value is optional)</p>
			<xsl:for-each select="tags/tag">
				<p class="custom_row">
					<input type="text" name="tag[]" value="{@name}" />: <input type="text" name="tag_value[]" value="{.}" />
				</p>
			</xsl:for-each>

			<!-- New tag -->
			<p class="custom_row"><input type="text" name="tag[]" />: <input type="text" name="tag_value[]" /></p>
			<p class="custom_row"><input type="text" name="tag[]" />: <input type="text" name="tag_value[]" /></p>
			<p class="custom_row"><input type="text" name="tag[]" />: <input type="text" name="tag_value[]" /></p>
			<p class="custom_row"><input type="text" name="tag[]" />: <input type="text" name="tag_value[]" /></p>
			<p class="custom_row"><input type="text" name="tag[]" />: <input type="text" name="tag_value[]" /></p>
			<p class="custom_row">To remove a tag, just leave the tag name and value blank.</p>
			<p class="custom_row">To get more tag boxes to write in, just save and more empy ones will appear.</p>

			<xsl:if test="../meta/action = 'add_content'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Add content'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_content'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save changes'" />
				</xsl:call-template>
			</xsl:if>

		</form>
	</xsl:template>

	<!-- Add content - - >
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'add_content']">
		<form method="post" action="content/add_content">

			<h2>Content types</h2>

			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_id_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<h2>Content</h2>
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'content'" />
				<xsl:with-param name="label" select="'Content:'" />
				<xsl:with-param name="type" select="'textarea'" />
				<xsl:with-param name="rows" select="'20'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add content'" />
			</xsl:call-template>
		</form>
	</xsl:template>

	<! - - Edit content - - >
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'edit_content']">
		<form method="post" action="content/edit_content/{content_id}">

			<h2>Content types</h2>

			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_id_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<h2>Content</h2>
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'content'" />
				<xsl:with-param name="label" select="'Content:'" />
				<xsl:with-param name="type" select="'textarea'" />
				<xsl:with-param name="rows" select="'20'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save'" />
			</xsl:call-template>
		</form>
	</xsl:template-->

</xsl:stylesheet>
