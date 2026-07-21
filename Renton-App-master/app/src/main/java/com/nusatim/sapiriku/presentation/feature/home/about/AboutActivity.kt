package com.nusatim.sapiriku.presentation.feature.home.about
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import com.nusatim.sapiriku.BuildConfig
import com.nusatim.sapiriku.databinding.ActivityAboutBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class AboutActivity : AppCompatActivity() {

    private lateinit var binding: ActivityAboutBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAboutBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        binding.txtBuildVersion.text = BuildConfig.VERSION_NAME
    }
}
