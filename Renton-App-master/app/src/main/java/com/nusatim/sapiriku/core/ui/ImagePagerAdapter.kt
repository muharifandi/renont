package com.nusatim.sapiriku.core.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.ImageView
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.R

/** Simple ViewPager2-backed image gallery, replacing the (now unresolvable) CarouselView library. */
class ImagePagerAdapter(
    private val urls: List<String>,
    @androidx.annotation.DrawableRes private val placeholder: Int = R.drawable.no_image,
    @androidx.annotation.DrawableRes private val errorDrawable: Int = R.drawable.no_image,
    private val onImageClick: (position: Int) -> Unit = {}
) : RecyclerView.Adapter<ImagePagerAdapter.ImageViewHolder>() {

    class ImageViewHolder(val imageView: ImageView) : RecyclerView.ViewHolder(imageView)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ImageViewHolder {
        val imageView = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_image_pager, parent, false) as ImageView
        return ImageViewHolder(imageView)
    }

    override fun onBindViewHolder(holder: ImageViewHolder, position: Int) {
        holder.imageView.load(urls[position]) {
            placeholder(placeholder)
            error(errorDrawable)
        }
        holder.imageView.setOnClickListener { onImageClick(position) }
    }

    override fun getItemCount(): Int = urls.size
}
