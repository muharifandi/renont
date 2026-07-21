package com.nusatim.sapiriku.core.ui.component

import android.content.Context
import android.util.AttributeSet
import android.view.LayoutInflater
import android.widget.FrameLayout
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.ViewSapirikuButtonBinding

/**
 * Custom Button Component untuk Sapiriku.
 * Mendukung state Loading, Disabled/Enabled, dan Type (Primary/Outline).
 */
class SapirikuButton @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : FrameLayout(context, attrs, defStyleAttr) {

    private val binding: ViewSapirikuButtonBinding =
        ViewSapirikuButtonBinding.inflate(LayoutInflater.from(context), this)

    private var originalText: CharSequence? = null
    private var isCurrentlyEnabled: Boolean = true
    private var buttonType: Int = 0

    init {
        context.obtainStyledAttributes(attrs, R.styleable.SapirikuButton).apply {
            val text = getString(R.styleable.SapirikuButton_android_text)
            val enabled = getBoolean(R.styleable.SapirikuButton_android_enabled, true)
            val isLoading = getBoolean(R.styleable.SapirikuButton_btnIsLoading, false)
            buttonType = getInt(R.styleable.SapirikuButton_btnType, 0)

            setText(text)
            setIsEnabled(enabled)
            setLoading(isLoading)
            applyTypeStyle()

            recycle()
        }
    }

    fun setText(text: CharSequence?) {
        originalText = text
        binding.innerButton.text = text
    }

    fun setIsEnabled(enabled: Boolean) {
        isCurrentlyEnabled = enabled
        binding.innerButton.isEnabled = enabled
    }

    fun setLoading(isLoading: Boolean) {
        binding.progressBar.isVisible = isLoading
        if (isLoading) {
            binding.innerButton.text = ""
            binding.innerButton.isEnabled = false
            
            val color = if (buttonType == 1) {
                ContextCompat.getColor(context, R.color.colorPrimary)
            } else {
                ContextCompat.getColor(context, R.color.white)
            }
            binding.progressBar.setIndicatorColor(color)
        } else {
            binding.innerButton.text = originalText
            binding.innerButton.isEnabled = isCurrentlyEnabled
        }
    }

    override fun setOnClickListener(listener: OnClickListener?) {
        binding.innerButton.setOnClickListener(listener)
    }

    private fun applyTypeStyle() {
        when (buttonType) {
            1 -> { // Outline
                binding.innerButton.apply {
                    strokeWidth = 2
                    strokeColor = ContextCompat.getColorStateList(context, R.color.colorPrimary)
                    backgroundTintList = ContextCompat.getColorStateList(context, android.R.color.transparent)
                    setTextColor(ContextCompat.getColor(context, R.color.colorPrimary))
                    rippleColor = ContextCompat.getColorStateList(context, R.color.soft_grey)
                }
            }
            else -> { // Primary
                binding.innerButton.apply {
                    backgroundTintList = ContextCompat.getColorStateList(context, R.color.colorPrimary)
                    setTextColor(ContextCompat.getColor(context, R.color.white))
                }
            }
        }
    }
}
