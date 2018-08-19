{strip}
	<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		</head>
		<body>
			<table style="width:98%; border: 1px solid #CCC; font-family:Verdana, Geneva, Arial, Helvetica, sans-serif; font-size:12px;">
				<tbody>
					<tr>
						<td>
							&nbsp;
						</td>
						<td>
							<p>Delft, {$smarty.now|date_format:"%e %B %Y"}</p>
						</td>
						<td style="text-align:right;">
							<img alt="Beeldmerk van de Vereniging" src="{$smarty.const.CSR_ROOT}/dist/images/beeldmerk.png" />
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;
						</td>
						<td>
							{$body}
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr style="height:75px;">
						<td>
							&nbsp;
						</td>
					</tr>
				</tbody>
			</table>
		</body>
	</html>
{/strip}