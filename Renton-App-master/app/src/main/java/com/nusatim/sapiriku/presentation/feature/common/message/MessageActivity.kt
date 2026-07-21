package com.nusatim.sapiriku.presentation.feature.common.message

import android.os.Bundle
import android.view.MenuItem
import android.view.View
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.custom.ButtonData
import com.nusatim.sapiriku.databinding.ActivityMessageBinding
import dagger.hilt.android.AndroidEntryPoint
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class MessageActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMessageBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMessageBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        title = intent.getStringExtra("title")
        binding.txtMessage.text = intent.getStringExtra("message")

        val imgResId = intent.getIntExtra("image", 0)
        if (imgResId != 0) {
            binding.imgPreview.setImageResource(imgResId)
        }

        val data = buttonData
        if (data != null) {
            binding.btnAction.text = data.text
            binding.btnAction.setOnClickListener(data.onClickListener)
        } else {
            binding.btnAction.isVisible = false
        }
        buttonData = null
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    companion object {
        var buttonData: ButtonData? = null
    }
}
