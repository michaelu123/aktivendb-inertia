<?php
use Illuminate\Support\HtmlString;
?>

<!DOCTYPE html>
<html>

<head>
  <base target="_top" />
</head>

<body>
  <p>
    {{ $anrede }},<br /><br />
    Durch die Antworten auf den Serienbrief zur AktivenDB-Aktualisierung ergeben sich für Eure AG/OG diese
    Änderungen:<br />

    @if (count($add) > 0)
      In die AG eintreten möchten<br /><br />
      {{ new HtmlString(implode("<br />\n", $add)) }}
      <br /><br />
    @endif
    @if (count($delete) > 0)
      Aus der AG ausgetreten (d.h. in der AktivenDB aus Eurer AG entfernt) sind:<br /><br />
      {{ new HtmlString(implode("<br />\n", $delete)) }}
      <br /><br />
    @endif
  </p>
  <p>Zur Erläuterung:</p>
  <p>
    Wenn ein Mitglied das Formular zur Aktualisierung der AktivenDB ausfüllt, sind Häkchen gesetzt
    für die AGs/OGs, in denen es als Mitglied geführt wird. Löscht es eins dieser Häkchen, wird es
    automatisch aus der AG/OG entfernt. Setzt es ein Häkchen bei einer neuen AG/OG,
    wird es aber nicht automatisch Mitglied der AG/OG. In beiden Fällen werdet Ihr als AG/OG-Leiter mit dieser Email
    informiert.
  </p>
  <p>
    Ihr könnt Euch dann mit den Mitgliedern austauschen, ob sie wirklich ein- oder austreten wollen.
    Danach könnt Ihr entweder den Austritt durch Wiederaufnahme rückgängig machen, falls z.B. ein Irrtun vorlag.
    Oder Ihr stimmt dem beabsichtigten Eintritt durch Hinzufügen des Mitglieds zu Eurer AG zu.
  </p>
  <p>
    Solltet Ihr nicht oder nicht mehr wissen, wie das in der AktivenDB geht,
    schickt uns eine kurze Email, und wir machen es für Euch, oder schicken Euch eine Anleitung,
    oder machen es mit Euch zusammen.
  </p>

  <p>
    Mit freundlichen Grüßen,<br />
    Aktivenmanagement<br />
    <br />
    Allgemeiner Deutscher Fahrrad-Club München e.V.<br />
    Platenstraße 4<br />
    80336 München<br />
    Tel. 089 | 773429<br />
    muenchen.adfc.de<br />
  </p>
</body>

</html>