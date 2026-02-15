import { Server } from "revolt.js";

export function report(object: Server) {
    let type;
    if (object instanceof Server) {
        type = "Server";
    }

    window.open(
        `mailto:support@armabattles.com?subject=${encodeURIComponent(
            `${type} Report`,
        )}&body=${encodeURIComponent(
            `${type} ID: ${object._id}\nWrite more information here!`,
        )}`,
    );
}
