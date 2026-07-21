package com.nusatim.sapiriku.core.ui.component

import android.content.Context
import android.text.InputType
import android.util.AttributeSet
import android.view.LayoutInflater
import android.widget.FrameLayout
import androidx.appcompat.content.res.AppCompatResources
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.ViewSapirikuInputBinding

/**
 * Custom Input Component untuk Sapiriku.
 * Mendukung Hint, InputType, Icon, Error Handling, Disabled state, dan Dropdown mode.
 */
class SapirikuInput @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : FrameLayout(context, attrs, defStyleAttr) {

    private val binding: ViewSapirikuInputBinding =
        ViewSapirikuInputBinding.inflate(LayoutInflater.from(context), this)

    init {
        context.obtainStyledAttributes(attrs, R.styleable.SapirikuInput).apply {
            val hint = getString(R.styleable.SapirikuInput_android_hint)
            val text = getString(R.styleable.SapirikuInput_android_text)
            val inputType = getInt(R.styleable.SapirikuInput_android_inputType, InputType.TYPE_CLASS_TEXT)
            val enabled = getBoolean(R.styleable.SapirikuInput_android_enabled, true)
            val startIcon = getResourceId(R.styleable.SapirikuInput_inputStartIcon, 0)
            val endIcon = getResourceId(R.styleable.SapirikuInput_inputEndIcon, 0)
            val isDropdown = getBoolean(R.styleable.SapirikuInput_inputIsDropdown, false)
            val error = getString(R.styleable.SapirikuInput_inputError)

            setHint(hint)
            setText(text)
            setInputType(inputType)
            setIsEnabled(enabled)
            setStartIcon(startIcon)
            setEndIcon(endIcon)
            setIsDropdown(isDropdown)
            setError(error)

            recycle()
        }
    }

    fun setHint(hint: CharSequence?) {
        binding.textInputLayout.hint = hint
    }

    fun setText(text: CharSequence?) {
        binding.editText.setText(text)
    }

    fun getText(): String = binding.editText.text.toString()

    fun setInputType(type: Int) {
        binding.editText.inputType = type
        
        // Auto-enable password toggle if input type is password
        if (type == InputType.TYPE_TEXT_VARIATION_PASSWORD || 
            type == (InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD)) {
            binding.textInputLayout.endIconMode = com.google.android.material.textfield.TextInputLayout.END_ICON_PASSWORD_TOGGLE
        }
    }

    fun setIsEnabled(enabled: Boolean) {
        binding.textInputLayout.isEnabled = enabled
        binding.editText.isEnabled = enabled
    }

    fun setStartIcon(resId: Int) {
        if (resId != 0) {
            binding.textInputLayout.startIconDrawable = AppCompatResources.getDrawable(context, resId)
        }
    }

    fun setEndIcon(resId: Int) {
        if (resId != 0) {
            binding.textInputLayout.endIconMode = com.google.android.material.textfield.TextInputLayout.END_ICON_CUSTOM
            binding.textInputLayout.endIconDrawable = AppCompatResources.getDrawable(context, resId)
        }
    }

    fun setError(error: String?) {
        binding.textInputLayout.error = error
        binding.textInputLayout.isErrorEnabled = !error.isNullOrEmpty()
    }

    fun setIsDropdown(isDropdown: Boolean) {
        if (isDropdown) {
            binding.editText.isFocusable = false
            binding.editText.isClickable = true
            binding.editText.isCursorVisible = false
            binding.textInputLayout.endIconMode = com.google.android.material.textfield.TextInputLayout.END_ICON_CUSTOM
            binding.textInputLayout.setEndIconDrawable(R.drawable.ic_arrow_drop_down)
        } else {
            binding.editText.isFocusable = true
            binding.editText.isFocusableInTouchMode = true
            binding.editText.isClickable = false
            binding.editText.isCursorVisible = true
        }
    }

    override fun setOnClickListener(l: OnClickListener?) {
        binding.editText.setOnClickListener(l)
    }

    fun getEditText() = binding.editText
    fun getTextInputLayout() = binding.textInputLayout
}
