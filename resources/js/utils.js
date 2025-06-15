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

export { parse, toDate, fromDate, toAbgegeben };
