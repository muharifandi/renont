package com.nusatim.sapiriku.presentation.feature.common.zoomimage

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.ActivityZoomImageListBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ZoomImageListActivity : AppCompatActivity() {

    private lateinit var binding: ActivityZoomImageListBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityZoomImageListBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val photo = intent.getStringExtra("photo")
        binding.image.load(photo) {
            placeholder(R.drawable.ic_time)
            error(R.drawable.ic_broken_image)
        }
    }
}
