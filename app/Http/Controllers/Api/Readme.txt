The Api endpoints are meant to be used by the aktdb_tool program, 
which is written in Python. This program must login with an admin user,
and the middleware CheckIsAdmin rejects calls where the user is not an admin.
Therefore, we do not check for other users or rights.

The Api middlewares called are TokenToHdr, auth/sanctum, CheckIsAdmin in this order. 
Only after sanctum $request->user() returns non-null. 
TokenToHdr and CheckIsAdmin are configured in bootstrap/app.php, sanctum in routes/api.php.

aktdb_tool calls originally API endpoints of aktivendb_backend, written with laravel lumen.
I did not want to change aktdb_tool, which sends the token as a query parameter instead of
an Authorization header, so TokenToHdr converts the query parameter to the header required 
by sanctum. Also, in aktivendb-inertia I changed project_team to just team in many places,
but aktdb_tool wants to see project_team. This is handled in App/Models/Member toJson(). 