package com.nusatim.sapiriku.presentation.feature.common.listsort

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.ArrayAdapter
import androidx.appcompat.app.AppCompatActivity
import com.nusatim.sapiriku.databinding.ActivityListSortBinding
import dagger.hilt.android.AndroidEntryPoint
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class ListSortActivity : AppCompatActivity() {

    private lateinit var binding: ActivityListSortBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityListSortBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        val items = intent.getStringArrayExtra("list_sort") ?: emptyArray()
        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_single_choice, items)

        binding.list.apply {
            choiceMode = android.widget.ListView.CHOICE_MODE_SINGLE
            setAdapter(adapter)
            setOnItemClickListener { _, _, position, _ ->
                val resultIntent = Intent().putExtra("sort", position)
                setResult(Activity.RESULT_OK, resultIntent)
                finish()
            }
            setItemChecked(intent.getIntExtra("sort", 0), true)
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(com.nusatim.sapiriku.R.anim.slide_in_left, com.nusatim.sapiriku.R.anim.slide_out_right)
    }
}
