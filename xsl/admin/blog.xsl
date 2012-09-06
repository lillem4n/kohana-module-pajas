<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<xsl:call-template name="tab">
				<xsl:with-param name="href"      select="'blog'" />
				<xsl:with-param name="text"      select="'Blogposts'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"      select="'blog/blogpost'" />
				<xsl:with-param name="text"      select="'Add blogpost'" />
				<xsl:with-param name="action"    select="'blogpost'" />
				<xsl:with-param name="url_param" select="''" />
			</xsl:call-template>
		</ul>
	</xsl:template>

	<xsl:template match="/">
		<xsl:if test="/root/content[../meta/controller = 'blog' and ../meta/action = 'index']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Blog'" />
				<xsl:with-param name="h1"    select="'Blog'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/content[../meta/controller = 'blog' and ../meta/action = 'blogpost' and not(../meta/url_params/id)]">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Blogpost'" />
				<xsl:with-param name="h1"    select="'Add blogpost'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/content[../meta/controller = 'blog' and ../meta/action = 'blogpost' and ../meta/url_params/id]">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Blogpost'" />
				<xsl:with-param name="h1"    select="'Edit blogpost'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List blogposts -->
	<xsl:template match="content[../meta/controller = 'blog' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="small_row">ID</th>
					<th>Datetime</th>
					<th>Title</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="contents/content">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="@id" /></td>
						<td><xsl:value-of select="tags/datetime" /></td>
						<td><xsl:value-of select="tags/title" /></td>
						<td>
							<xsl:text>[</xsl:text><a>
							<xsl:attribute name="href">
								<xsl:text>blog/blogpost?id=</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Edit</xsl:text>
							</a>] [<a>
							<xsl:attribute name="href">
								<xsl:text>blog/blogpost?rm&amp;id=</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Delete</xsl:text>
							</a><xsl:text>]</xsl:text>
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<!-- Add or edit blogpost -->
	<xsl:template match="content[../meta/controller = 'blog' and ../meta/action = 'blogpost']">
		<form method="post">
			<xsl:if test="../meta/url_params/id">
				<xsl:attribute name="action">
					<xsl:text>blog/blogpost?id=</xsl:text>
					<xsl:value-of select="../meta/url_params/id" />
				</xsl:attribute>
			</xsl:if>

			<!-- ID -->
			<xsl:if test="../meta/url_params/id">
				<xsl:call-template name="form_line">
					<xsl:with-param name="type"  select="'none'" />
					<xsl:with-param name="label" select="'Blogpost Id:'" />
					<xsl:with-param name="value" select="../meta/url_params/id" />
				</xsl:call-template>
			</xsl:if>

			<!-- Datetime -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id"    select="'datetime'" />
				<xsl:with-param name="label" select="'Date and time:'" />
			</xsl:call-template>

			<!-- Title -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id"    select="'title'" />
				<xsl:with-param name="label" select="'Title:'" />
			</xsl:call-template>

			<!-- Content -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'content'" />
				<xsl:with-param name="label" select="'Content:'" />
				<xsl:with-param name="type" select="'textarea'" />
				<xsl:with-param name="rows" select="'20'" />
			</xsl:call-template>

			<xsl:if test="../meta/url_params/id">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save changes'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(../meta/url_params/id)">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Add blogpost'" />
				</xsl:call-template>
			</xsl:if>

		</form>

	</xsl:template>

</xsl:stylesheet>
