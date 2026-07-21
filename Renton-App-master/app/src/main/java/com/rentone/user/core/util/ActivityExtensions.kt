package com.rentone.user.core.util

import android.app.Activity
import android.graphics.BlendMode
import android.graphics.BlendModeColorFilter
import android.graphics.PorterDuff
import android.graphics.drawable.Drawable
import android.os.Build
import androidx.annotation.AnimRes
import androidx.annotation.ColorInt
import androidx.activity.ComponentActivity

/** Version-safe replacement for the deprecated [Activity.overridePendingTransition]. */
fun Activity.applyExitTransition(@AnimRes enterAnim: Int, @AnimRes exitAnim: Int) {
    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
        overrideActivityTransition(Activity.OVERRIDE_TRANSITION_CLOSE, enterAnim, exitAnim)
    } else {
        @Suppress("DEPRECATION")
        overridePendingTransition(enterAnim, exitAnim)
    }
}

/** Version-safe replacement for the deprecated [Drawable.setColorFilter] (Int, PorterDuff.Mode) overload. */
fun Drawable.setColorFilterCompat(@ColorInt color: Int, mode: PorterDuff.Mode) {
    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
        colorFilter = BlendModeColorFilter(color, BlendMode.SRC_IN)
    } else {
        @Suppress("DEPRECATION")
        setColorFilter(color, mode)
    }
}

/**
 * Registers [handler] as this Activity's back-press behavior, replacing the deprecated
 * `override fun onBackPressed()` pattern. [handler] receives the callback so it can call
 * `isEnabled = false` before deferring to the next handler (e.g. `onBackPressedDispatcher.onBackPressed()`)
 * when it wants default back navigation to proceed.
 */
fun ComponentActivity.setBackPressedHandler(handler: androidx.activity.OnBackPressedCallback.() -> Unit) {
    onBackPressedDispatcher.addCallback(this, object : androidx.activity.OnBackPressedCallback(true) {
        override fun handleOnBackPressed() = handler()
    })
}
