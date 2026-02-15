import { Brush } from "@styled-icons/boxicons-solid";
import { observer } from "mobx-react-lite";
import { Link } from "react-router-dom";
// @ts-expect-error shade-blend-color does not have typings.
import pSBC from "shade-blend-color";

import { Text } from "preact-i18n";

import { CategoryButton, ObservedInputElement } from "@revoltchat/ui";

import { useApplicationState } from "../../../mobx/State";

import { ThemeBaseSelector } from "./legacy/ThemeBaseSelector";

/**
 * ! LEGACY
 * Component providing a way to switch the base theme being used.
 */
export const ShimThemeBaseSelector = observer(() => {
    const theme = useApplicationState().settings.theme;
    return (
        <ThemeBaseSelector
            value={theme.isModified() ? undefined : theme.getBase()}
            setValue={(base) => {
                theme.setBase(base);
                theme.reset();
            }}
        />
    );
});

export default function ThemeSelection() {
    return (
        <>
            {/** Allow users to change between light and dark mode */}
            <ShimThemeBaseSelector />
            {/** Arma Battles theme is the standard - no customization */}
        </>
    );
}
