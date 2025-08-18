function parse(data) {
    if (typeof data.record_new !== "string") return data;
    try {
        data.record_new = JSON.parse(data.record_new);
        data.record_old = JSON.parse(data.record_old);
        for (const prop in data) {
            if (prop.endsWith("_id")) {
                data[prop] = data[prop].toString();
            }
        }
        for (const prop in data.record_new) {
            if (prop.endsWith("_id")) {
                data.record_new[prop] = data.record_new[prop].toString();
            }
        }
        for (const prop in data.record_old) {
            if (prop.endsWith("_id")) {
                data.record_old[prop] = data.record_old[prop].toString();
            }
        }
        // eslint-disable-next-line no-empty
    } catch (e) {}
    return data;
}

function toDate(t) {
    let d;
    if (t == null) {
        d = null;
    } else {
        let y = parseInt(t.substring(0, 4));
        let m = parseInt(t.substring(5, 7));
        let dy = parseInt(t.substring(8, 10));
        d = new Date(y, m - 1, dy, 6);
    }
    return d;
}

function fromDate(d) {
    if (!d) return "";
    d.setTime(d.getTime() + 3 * 60 * 60 * 1000); // convert from UTC 2024-01-01 00:00:00 to 2024-01-01 03:00:00
    return d.toISOString().substring(0, 10);
    // return (
    //   '' +
    //   d.getFullYear() +
    //   '-' +
    //   ('0' + (d.getMonth() + 1)).slice(-2) +
    //   '-' +
    //   ('0' + d.getDate()).slice(-2)
    // )
}

function toAbgegeben(v) {
    if (v == null) return "-";
    if (v == 0) return "irrelevant";
    if (v == 1) return "abgegeben";
    if (v == 2) return "noch nicht abgegeben";
    return "??? " + v;
}

function checkForTrue(val) {
    if (val === true || val == "1" || val == 1) {
        return true;
    }
    return false;
}

function checkForFalse(val) {
    if (val === false || val == "0" || val == 0) {
        return true;
    }
    return false;
}

function makeSchema(members, preferredEmail) {
    let ags = new Set();
    if (members != null) {
        for (let m of members) {
            for (let ag of m.ags ?? []) {
                ags.add(ag);
            }
        }
    }

    let schema = [
        /*
          {
              column: "Name",
              type: String,
              value: (member) => member.name,
              width: 30,
          },
          */
        {
            column: "Nachname",
            type: String,
            value: (member) => member.last_name,
            width: 30,
        },
        {
            column: "Vorname",
            type: String,
            value: (member) => member.first_name,
            width: 30,
        },
        {
            column: "Geschlecht",
            type: String,
            value: (member) => member.gender,
            width: 10,
        },
        {
            column: "Geburtsjahr",
            type: String,
            value: (member) => member.birthday,
            width: 12,
        },
        {
            column: "Postleitzahl",
            type: String,
            value: (member) => member.address,
            width: 12,
        },
        {
            column: "ADFC-Mitgliedsnummer",
            type: Number,
            value: (member) =>
                member.adfc_id == null ? null : parseInt(member.adfc_id),
            width: 22,
        },
        {
            column: "Email-ADFC",
            type: String,
            value: (member) => member.email_adfc,
            width: 30,
        },
        {
            column: "Email-Privat",
            type: String,
            value: (member) => member.email_private,
            width: 30,
        },
        {
            column: "Email",
            type: String,
            value: function (member) {
                let email = "";
                if (preferredEmail.endsWith("ADFC")) {
                    email =
                        member.email_adfc != ""
                            ? member.email_adfc
                            : member.email_private;
                } else {
                    email =
                        member.email_private != ""
                            ? member.email_private
                            : member.email_adfc;
                }
                return email;
            },
            width: 30,
        },
        {
            column: "Telefon",
            type: String,
            value: (member) => member.phone_primary,
            width: 20,
        },
        {
            column: "Telefon-Alternative",
            type: String,
            value: (member) => member.phone_secondary,
            width: 20,
        },
        {
            column: "AGs",
            type: String,
            value: (member) => member.agAll,
            width: 30,
        },
        {
            column: "Interessen",
            type: String,
            value: (member) => member.interests,
            width: 30,
        },
        {
            column: "Status",
            type: String,
            value: (member) => member.status,
            width: 30,
        },
        {
            column: "Letztes Erste-Hilfe-Training",
            type: Date,
            format: "yyyy-mm-dd",
            value: function (member) {
                let t = member.latest_first_aid_training;
                return toDate(t);
            },
            width: 15,
        },
        {
            column: "Nächstes Erste-Hilfe-Training",
            type: Date,
            format: "yyyy-mm-dd",
            value: function (member) {
                let t = member.next_first_aid_training;
                return toDate(t);
            },
            width: 15,
        },
        {
            column: "Fragebogen ausgefüllt",
            type: Boolean,
            value: (member) => member.responded_to_questionaire == "1",
            width: 15,
        },
        {
            column: "Datum Fragebogen",
            type: Date,
            format: "yyyy-mm-dd",
            value: function (member) {
                let t = member.responded_to_questionaire_at;
                return toDate(t);
            },
            width: 15,
        },
        {
            column: "Aktiv",
            type: Boolean,
            value: (member) => member.active == "1",
            width: 15,
        },
        {
            column: "DSGVO",
            type: String,
            value: (member) => toAbgegeben(member.dsgvo_signature),
            width: 30,
        },
        {
            column: "Erweitertes Führungszeugnis",
            type: String,
            value: (member) => toAbgegeben(member.police_certificate),
            width: 30,
        },
        {
            column: "Datum Führungszeugnis",
            type: Date,
            format: "yyyy-mm-dd",
            value: function (member) {
                let t = member.polcert_date;
                return toDate(t);
            },
            width: 15,
        },
    ];

    for (let ag of [...ags].sort()) {
        schema.push({
            column: ag,
            type: Boolean,
            value: (member) => member.ags.includes(ag),
            width: 20,
        });
    }
    return schema;
}

export {
    parse,
    toDate,
    fromDate,
    toAbgegeben,
    checkForTrue,
    checkForFalse,
    makeSchema,
};
