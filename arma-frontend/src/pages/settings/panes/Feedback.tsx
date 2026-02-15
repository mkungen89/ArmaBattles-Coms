import { Github } from "@styled-icons/boxicons-logos";
import { BugAlt, Group, ListOl } from "@styled-icons/boxicons-regular";
import { Link } from "react-router-dom";

import styles from "./Panes.module.scss";
import { Text } from "preact-i18n";

import { CategoryButton, Column, Tip } from "@revoltchat/ui";

export function Feedback() {
    return (
        <Column>
            <div className={styles.feedback}>
                <a
                    href="https://armabattles.com/support"
                    target="_blank"
                    rel="noreferrer">
                    <CategoryButton
                        action="external"
                        icon={<Github size={24} />}
                        description="Get help and support for Arma Battles Chat">
                        Support
                    </CategoryButton>
                </a>
                <a
                    href="https://armabattles.com/contact"
                    target="_blank"
                    rel="noreferrer">
                    <CategoryButton
                        action="external"
                        icon={<ListOl size={24} />}
                        description="Contact the Arma Battles team">
                        Contact Us
                    </CategoryButton>
                </a>
            </div>
        </Column>
    );
}
